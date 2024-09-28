<?php

namespace App\Http\Controllers\Api;

use App\Models\Like;
use App\Models\LikeItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class LikeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $likeItem = LikeItem::with(['like'], ['product'])->get();
        return new MasterResource(true, 'List data yang ada di like item', $likeItem);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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
        $like = Like::where('user_id', $userID->id)->first();

        if (!$like) {
            return response()->json(['error' => 'Like tidak ditemukan'], 404);
        }

        $product = Product::find($request->product_id); // Menggunakan find untuk mendapatkan produk

        // Cek apakah produk ada
        if (!$product) {
            return response()->json(['error' => 'Product tidak ditemukan'], 404);
        }

        // Cari item di like
        $likeItem = LikeItem::where('like_id', $like->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($likeItem) {
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

            // Tambahkan quantity ke like item yang sudah ada
            $likeItem->quantity += $request->quantity;

            if ($likeItem->quantity > $product->stock) {
                return response()->json([
                    'message' => 'Kekurangan stock pada produk: ' . $product->name
                ], 400);
            }

            $likeItem->save();

            return response()->json([
                'message' => 'Quantity bertambah.'
            ], 200);
        }

        // Jika produk belum ada di like, buat item baru
        if ($request->quantity <= 0) {
            return response()->json(['message' => 'Jumlah yang anda masukan salah'], 400);
        }

        // Pengecekan stok produk
        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Kekurangan stock pada produk: ' . $product->name
            ], 400);
        }

        // Buat item baru di like
        $likeItem = LikeItem::create([
            'like_id' => $like->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data berhasil di tambahkan kedalam like', $likeItem);
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

        $likeItem = LikeItem::find($id);
        $likeItem->update([
            'quantity' => $request->quantity,
        ]);

        return new MasterResource(true, 'Data like item berhasil di ubah', $likeItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $likeItem = LikeItem::find($id);
        $likeItem->delete();
        return new MasterResource(true, 'Data like item berhasil di hapus', null);
    }
}
