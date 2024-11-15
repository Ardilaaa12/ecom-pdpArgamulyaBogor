<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mengambil ID pengguna yang sedang login
        $userId = Auth::id();

        CartItem::whereHas('cart', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->update(['status' => false]);

        // Mengambil data cart yang terkait dengan pengguna dan memuat relasi cartItems
        $cart = Cart::where('user_id', $userId)
                    ->with(['cartItems.product'])
                    ->get();

        // Menghitung total dari item dengan `status` bernilai `true`
        $total = $cart->flatMap->cartItems
                    ->filter(fn($item) => $item->status)
                    ->sum(fn($item) => floatval($item->product->price) * $item->quantity);

        return new MasterResource(true, 'List cart berhasil ditampilkan', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    // customer
    public function updateStatus(Request $request, $itemId)
    {
        // Memastikan status ada di dalam request
        if (!$request->has('status')) {
            return response()->json(['message' => 'Status is required'], 400);
        }

        $cartItem = CartItem::where('id', $itemId)
                    ->whereHas('cart', function($query) {
                        $query->where('user_id', auth()->id());
                    })->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        // Update status item
        $cartItem->update(['status' => $request->status]);

        return $this->getTotal();
    }

    function getTotal()
    {
        $cart = Cart::where('user_id', auth()->id())
                    ->with(['cartItems.product'])
                    ->first(); // Mengambil hanya satu cart
    
        if (!$cart) {
            return 0;
        }
    
        // Menghitung total dari item dengan `status` bernilai `true`
        $total = $cart->cartItems
                    ->filter(fn($item) => $item->status)
                    ->sum(fn($item) => floatval($item->product->price) * $item->quantity);
    
        // return $total;

        return response()->json([
            'message' => 'Berhasil diubah!',
            'total' => $total,
        ]);
    }
    


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userID = Auth::user();
        $cart = Cart::where('user_id', $userID->id)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart tidak ditemukan'], 404);
        }

        $product = Product::find($request->product_id); // Menggunakan find untuk mendapatkan produk

        // Cek apakah produk ada
        if (!$product) {
            return response()->json(['error' => 'Product tidak ditemukan'], 404);
        }

        // Cari item di cart
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            // Jika item sudah ada, tambahkan quantity
            if ($request->quantity <= 0) {
                return response()->json(['message' => 'Jumlah yang anda masukan salah'], 400);
            }

            // Pengecekan stok produk
            if ($request->quantity > $product->stock) {
                return response()->json([
                    'message' => 'Kekurangan stock pada produk: ' . $product->name
                ], 400);
            }

            // Tambahkan quantity ke cart item yang sudah ada
            $cartItem->quantity += $request->quantity;

            if ($cartItem->quantity > $product->stock) {
                return response()->json([
                    'message' => 'Kekurangan stock pada produk: ' . $product->name
                ], 400);
            }

            $cartItem->save();

            return response()->json([
                'message' => 'Quantity bertambah.'
            ], 200);
        }

        // Jika produk belum ada di cart, buat item baru
        if ($request->quantity <= 0) {
            return response()->json(['message' => 'Jumlah yang anda masukan salah'], 400);
        }

        // Pengecekan stok produk
        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Kekurangan stock pada produk: ' . $product->name
            ], 400);
        }

        // Buat item baru di cart
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data berhasil di tambahkan kedalam cart', $cartItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cartItem = CartItem::find($id);
        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        $cartItem->fresh();

        return $this->getTotal();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartItem = CartItem::find($id);

        $cartItem->delete();
        return new MasterResource(true, 'Data yang berada di cart item berhasil di hapus', null);
    }
}
