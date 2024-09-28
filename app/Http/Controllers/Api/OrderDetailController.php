<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Like;
use App\Models\LikeItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class OrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $OrderDetail = OrderDetail::all()->map(function ($order) {
            $order->price_unit = number_format($order->price_unit, 0, ',', '.');
            $order->sub_total = number_format($order->sub_total, 0, ',', '.');
            return $order; // Kembalikan objek yang telah dimodifikasi
        });
    
        return new MasterResource(true, "List data yang ada di Order Detail", $OrderDetail);
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

    public function generateRefOrder() {
        // mengambil order terakhir berdasarkan id untuk memastikan nomor berikutnya 
        $latestOrder = Order::latest('id')->first(); //mengambil order dengan ID terbesar 
        $number = $latestOrder ? $latestOrder->id + 1 : 1; //jika ada order terakhir, ambil ID dan tambahkan 1, jika tidak ada maka mulai dari 1 

        // membuat nomor referensi order dengan format REF-00001
        return 'REF-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request) 
    {
        $source = $request->input('source');
        $user = Auth::user();

        if ($source === 'direct') {
            $productId = $request->input('product_id'); // Ambil ID produk dari request
            $quantity = $request->input('quantity'); // Ambil jumlah dari request
    
            return $this->checkoutDirectly($user, $productId, $quantity);
        }

        $selectedItems = $request->input('selected_items');
        
        if (empty($selectedItems)) {
            return response()->json(['message' => 'No items selected for checkout'], 400);
        }

        switch ($source) {
            case 'cart':
                return $this->checkoutFromCart($user, $selectedItems);

            case 'likes':
                return $this->checkoutFromLikes($user, $selectedItems);

            default:
                return response()->json(['message' => 'Invalid checkout source'], 400);
        }
    }

    public function checkoutDirectly($user, $productId, $quantity)
    {
        // Temukan produk berdasarkan ID
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Cek stok produk
        if ($product->stock < $quantity) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        $price = (int) str_replace(',', '', $product->price);
        $totalPrice = $price * $quantity;

        // Buat order
        $order = Order::create([
            'user_id' => $user->id,
            'no_ref_order' => $this->generateRefOrder(),
            'total_amount' => $totalPrice,
            'order_date' => now(),
            'status' => 'menunggu pembayaran',
        ]);

        // Simpan detail order
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_unit' => $price,
            'sub_total' => $totalPrice,
        ]);

        // Kurangi stok produk
        $product->stock -= $quantity;
        $product->save();

        return response()->json(['message' => 'Order created successfully', 'order_id' => $order->id], 201);
    }

    public function checkoutFromCart($user, $selectedItems)
    {
        $totalPrice = 0;
        $cart = Cart::where('user_id', $user->id)->first();

        // Proses item yang dipilih dari cart
        foreach ($selectedItems as $productId) {
            $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->first();
            if ($cartItem) {
                $product = Product::find($cartItem->product_id);

                // mengecek apakah data minus atau tidak 
                if ($cartItem->quantity < 0) {
                    return response()->json([
                        'message' => 'Jumlah untuk produk: ' . $product->name_product . ' tidak bisa negatif.'
                    ], 400);
                }
        
                // Pengecekan stok produk
                if ($cartItem->quantity > $product->stock) {
                    return response()->json([
                        'message' => 'Kekurangan stock pada produk: ' . $product->name_product
                    ], 400);
                }

                $price = (int) str_replace(',', '', $product->price);
                $subtotal = $price * $cartItem->quantity;
                $totalPrice += $subtotal;
            }
        }

        if ($totalPrice == 0) {
            return response()->json(['message' => 'No valid items selected'], 400);
        }

        // Buat order
        $order = Order::create([
            'user_id' => $user->id,
            'no_ref_order' => $this->generateRefOrder(),
            'total_amount' => $totalPrice,
            'order_date' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'status' => 'menunggu pembayaran',
        ]);

        // Simpan detail order
        foreach ($selectedItems as $productId) {
            $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->first();
            if ($cartItem) {
                $product = Product::find($cartItem->product_id);
                $price = (int) str_replace(',', '', $product->price);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price_unit' => $price,
                    'sub_total' => $price * $cartItem->quantity,
                ]);

                // Kurangi stok produk
                $product->stock -= $cartItem->quantity;
                $product->save();
            }
        }

        // Kosongkan item yang dipilih dari cart
        CartItem::where('cart_id', $cart->id)->whereIn('product_id', $selectedItems)->delete();

        return response()->json(['message' => 'Order created successfully'], 200);
    }

    public function checkoutFromLikes($user, $selectedItems)
    {
        $totalPrice = 0;
        $like = Like::where('user_id', $user->id)->first();

        // Proses item yang dipilih dari like
        foreach ($selectedItems as $productId) {
            $likeItem = LikeItem::where('like_id', $like->id)->where('product_id', $productId)->first();
            if ($likeItem) {
                $product = Product::find($likeItem->product_id);

                if ($likeItem->quantity < 0) {
                    return response()->json([
                        'message' => 'Jumlah untuk produk: ' . $product->name_product . ' tidak bisa negatif.'
                    ], 400);
                }
        
                // Pengecekan stok produk
                if ($likeItem->quantity > $product->stock) {
                    return response()->json([
                        'message' => 'Kekurangan stock pada produk: ' . $product->name_product
                    ], 400);
                }

                $price = (int) str_replace(',', '', $product->price);
                $subtotal = $price * $likeItem->quantity;
                $totalPrice += $subtotal;
            }
        }

        if ($totalPrice == 0) {
            return response()->json(['message' => 'No valid items selected'], 400);
        }

        // Buat order
        $order = Order::create([
            'user_id' => $user->id,
            'no_ref_order' => $this->generateRefOrder(),
            'total_amount' => $totalPrice,
            'order_date' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'status' => 'menunggu pembayaran',
        ]);

        // Simpan detail order
        foreach ($selectedItems as $productId) {
            $likeItem = LikeItem::where('like_id', $like->id)->where('product_id', $productId)->first();
            if ($likeItem) {
                $product = Product::find($likeItem->product_id);
                $price = (int) str_replace(',', '', $product->price);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $likeItem->quantity,
                    'price_unit' => $price,
                    'sub_total' => $price * $likeItem->quantity,
                ]);

                // Kurangi stok produk
                $product->stock -= $likeItem->quantity;
                $product->save();
            }
        }

        // Kosongkan item yang dipilih dari cart
        LikeItem::where('like_id', $like->id)->whereIn('product_id', $selectedItems)->delete();

        return response()->json(['message' => 'Order created successfully'], 200);
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
    // public function update(Request $request, string $id)
    // {
    //     DB::beginTransaction();
    
    //     try {
    //         // Ambil order berdasarkan ID yang akan di-update
    //         $order = Order::findOrFail($id);
    
    //         // Update data order
    //         $order->update([
    //             'user_id' => $request->user_id,
    //             'no_ref_order' => $this->generateRefOrder(), // Fungsi untuk membuat nomor referensi
    //             'order_date' => now(),
    //             'status' => 'menunggu pembayaran',
    //         ]);
    
    //         // Menghitung total amount
    //         $total_amount = 0;
    
    //         // array untuk menyimpan stok produk yang akan dikembalikan jika terjadi error
    //         $updateStokProduct = [];
    
    //         // Looping untuk setiap order detail
    //         foreach ($request->order_details as $detail) {
    //             // Dapatkan data produk berdasarkan product_id
    //             $product = Product::findOrFail($detail['product_id']);
    
    //             // Validasi apakah quantity adalah angka
    //             $quantity = (int) $detail['quantity'];
    //             $price_unit = (float) $product->price;
    
    //             if (!is_numeric($quantity) || $quantity <= 0) {
    //                 return response()->json(['error' => 'Quantity harus berupa angka yang valid dan lebih dari 0'], 400);
    //             }
    
    //             // Update atau buat entri di tabel OrderDetail
    //             OrderDetail::updateOrCreate(
    //                 [
    //                     'order_id' => $order->id,
    //                     'product_id' => $product->id,
    //                 ],
    //                 [
    //                     'quantity' => $quantity,
    //                     'price_unit' => $price_unit,
    //                     'sub_total' => $quantity * $price_unit,
    //                 ]
    //             );
    
    //             // Mengurangi stok produk
    //             if ($product->stock < $quantity) {
    //                 throw new \Exception('Stok produk tidak mencukupi');
    //             }
    //             $product->stock -= $quantity;
    //             $product->save();
    
    //             // Menyimpan perubahan stok ke dalam array untuk rollback jika terjadi error
    //             $updateStokProduct[] = [
    //                 'product' => $product,
    //                 'quantity' => $quantity
    //             ];
    
    //             // Menambahkan ke total amount
    //             $total_amount += $quantity * $price_unit;
    //         }
    
    //         // Memperbarui total amount pada order
    //         $order->update([
    //             'total_amount' => $total_amount
    //         ]);
    
    //         // Commit transaksi jika tidak ada error
    //         DB::commit();
    
    //         return new MasterResource(true, 'Order berhasil diproses dengan baik', $order);
    
    //     } catch (\Exception $e) {
    //         // Rollback transaksi jika terjadi error
    //         DB::rollBack();
    
    //         // Mengembalikan stok produk jika terjadi error
    //         foreach ($updateStokProduct as $item) {
    //             $product = $item['product'];
    //             $product->stock += $item['quantity'];
    //             $product->save();
    //         }
    
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $OrderDetail = OrderDetail::findOrFail($id);

            // mengembalikan stok produk 
            $product = Product::findOrFail($OrderDetail->product_id);
            $product->stock += $OrderDetail->quantity;
            $product->save();

            // hapus detail pesanan 
            $OrderDetail->delete();

            DB::commit();

            return new MasterResource(true, 'Berhasil menghapus detail pesanan', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function cancleOrder() {
    //     DB::beginTransaction();

    //     try {
    //         $order = Order::with
    //     }
    // }
}
