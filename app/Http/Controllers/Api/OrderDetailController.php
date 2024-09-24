<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\CartItem;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class OrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $OrderDetail = OrderDetail::latest()->get();
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
        DB::beginTransaction();

        try {
            // membuat entri baru di tabel order 
            $order = Order::create([
                'user_id' => $request->user_id,
                'no_ref_order' => $this->generateRefOrder(), // Fungsi untuk membuat nomor referensi
                'order_date' => now(),
                'status' => 'menunggu pembayaran',
            ]);

               // Menghitung total amount
                $total_amount = 0;

                // array untuk menyimpan stok produk yang akan dikembalikan jika gagal 
                $updateStokProduct = [];

            //membuat entri di tabel OrderDetail 
            foreach ($request->order_details as $detail) {
                $product = Product::findOrFail($detail['product_id']);
               
                 // Validasi apakah quantity adalah angka
                $quantity = (int) $detail['quantity'];
                $price_unit = (float) $product->price;

                if (!is_numeric($quantity) || !is_numeric($price_unit)) {
                    return response()->json(['error' => 'Quantity atau Price tidak valid'], 400);
                }

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_unit' => $price_unit,
                    'sub_total' => $quantity * $price_unit,
                ]);

                // mengurangi stok produk jika pesanan dikonfirmasi 
                $product->stock -= $quantity;
                $product->save();

                // menyimpan perubahan stok ke dalam array untuk rollback 
                $updateStokProduct[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];

                 // Menambahkan ke total amount
                $total_amount += $quantity * $price_unit;
            }

             // Memperbarui total amount pada order
            $order->total_amount = $total_amount;
            $order->save();

            DB::commit();

            return new MasterResource(true, 'order berhasil terproses dengan baik', $order);

        } catch (\Exception $e) {
            DB::rollBack();

            // mengembalikan stok produk jika terjadi error 
            foreach ($updateStokProduct as $item) {
                $product = $item['product'];
                $product->stock += $item['quantity'];
                $product->save();
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
