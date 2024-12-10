<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Like;
use App\Models\LikeItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Shipping;
use App\Models\ShippingCost;
use App\Models\Payment;
use App\Models\Rekening;
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
    //  role admin (all)
    public function index()
    {
        $orders = Order::with(['user', 'orderDetail.product'])->get()->map(function ($order) {
            $order->total_amount = number_format(floatval($order->total_amount), 0, ',', '.');
            $order->orderDetail->map(function ($orderDetail) {

                $orderDetail->price_unit = number_format(floatval($orderDetail->price_unit), 0, ',', '.');
                $orderDetail->sub_total = number_format(floatval($orderDetail->sub_total), 0, ',', '.');
    
                $orderDetail->product->price = number_format(floatval($orderDetail->product->price), 0, ',', '.');
    
                return $orderDetail;
            });
    
            return $order;
        });
    
        return new MasterResource(true, "List data yang ada di Order Detail", $orders);
    }
    
    

    // role user (login sj)
    public function see()
    {
        $order = Order::with(['orderDetail.product','user'])
            ->whereHas('user', function ($query) {
                $query->where('id', auth()->user()->id);
            })
            ->get()
            ->map(function ($order) {
                // Looping melalui setiap orderDetail karena bisa ada lebih dari satu
                $order->orderDetail->each(function ($orderDetail) {
                    $orderDetail->price_unit = number_format($orderDetail->price_unit, 0, ',', '.');
                    $orderDetail->sub_total = number_format($orderDetail->sub_total, 0, ',', '.');
                });

                return $order;
            });

        return new MasterResource(true, "List data yang ada di Order Detail", $order);

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
        // mengambil id dari order terakhir
        $latestOrder = Order::latest('id')->first(); //mengambil order dengan ID terbesar 
        $number = $latestOrder ? $latestOrder->id + 1 : 1;

        // membuat nomor referensi order dengan format REF-00001
        return 'REF-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request) 
    {
        $user = Auth::user();
        if (empty($user->fullname) || empty($user->phone_number) || empty($user->address)) {
            return response()->json([
                'message' => 'Data user belum lengkap. Harap lengkapi nama lengkap, nomor telepon, dan alamat.'
            ], 400);
        }

        $cart = Cart::where('user_id', $user->id)->first();

        $selectedItems = CartItem::where('cart_id', $cart->id)
                                ->where('status', 1)
                                ->pluck('product_id')
                                ->toArray();

        $shippingDate = $request->input('shipping_date');
        $address = $request->input('address');
        $shippingCostId = $request->input('shipping_cost');
        $paymentMethod = $request->input('payment_method');
        $notes = $request->input('notes');
        
        if (empty($selectedItems)) {
            return response()->json(['message' => 'Tidak ada item yang dipilih'], 400);
        }

        $shippingCost = ShippingCost::find($shippingCostId);
        if (!$shippingCost) {
            return response()->json(['message' => 'Maaf, Domba belum bisa diantar ke tempat anda']);
        }

        $payment = Rekening::where('id', $paymentMethod)->exists();
        if (!$payment) {
            return response()->json(['message' => 'Metode pembayaran tidak valid'], 400);
        }
        
        return $this->checkoutFromCart($user, $selectedItems, $shippingDate, $address, $shippingCost, $paymentMethod, $notes);

    }

    public function checkoutFromCart($user, $selectedItems, $shippingDate, $address, $shippingCost, $paymentMethod, $notes)
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
            return response()->json(['message' => 'Barang tidak ada di cart!'], 400);
        }

        $totalPrice += $shippingCost->cost;

        // Buat order
        $order = Order::create([
            'user_id' => $user->id,
            'no_ref_order' => $this->generateRefOrder(),
            'total_amount' => $totalPrice,
            'order_date' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'status' => 'menunggu pembayaran',
            'notes' => $notes ?? '-',
        ]);

        $shippingAddress = !empty($address) ? $address : $user->address;

        Shipping::create([
            'order_id' => $order->id,
            'shipping_cost_id' => $shippingCost->cost,
            'shipping_date' => $shippingDate,
            'shipping_address' => $shippingAddress,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_master_id' => $paymentMethod,
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
        // return new MasterResource(true, "List data yang ada di Order Detail", $notes);
    }

    public function updateStatusBerhasil(string $id)
    {
        // $user = Auth::user();
        // $name = $user->fullname;

        // Cari order berdasarkan kolom order_id di order_detail
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan untuk order detail ini'], 404);
        }

        // Update status order menjadi 'berhasil' dan set check_by dalam satu langkah
        $order->update([
            'status' => 'berhasil',  // Ubah status menjadi 'berhasil'
            // 'check_by' => $name,
        ]);

        // Cek dan update status pengiriman jika ada data di tabel shipping
        $shipping = Shipping::where('order_id', $order->id)->first();
        if ($shipping) {
            $shipping->update(['shipping_status' => 'disiapkan']);
        }

        return response()->json([
            'message' => 'Status order berhasil diperbarui',
            'status' => $order->status,
        ]);
    }



    public function updateStatusGagal(string $id)
    {
        // $user = Auth::user();
        // $name = $user->fullname;

        // Cari order berdasarkan kolom order_id di order_detail
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan untuk order detail ini'], 404);
        }

        // Update status order menjadi 'berhasil' dan set check_by dalam satu langkah
        $order->update([
            'status' => 'gagal',  // Ubah status menjadi 'berhasil'
            // 'check_by' => $name,
        ]);

        // Cek dan update status pengiriman jika ada data di tabel shipping
        $shipping = Shipping::where('order_id', $order->id)->first();
        if ($shipping) {
            $shipping->update(['shipping_status' => '-']);
        }

        return response()->json([
            'message' => 'Status order berhasil diperbarui',
            'status' => $order->status,
        ]);
    }


    // function untuk admin
    public function show(string $id)
    {
        // Mencari order berdasarkan ID
        $orders = Order::with(['user', 'orderDetail.product'])->find($id);
        
        // Memeriksa apakah order detail ditemukan
        if ($orders) {
            return response()->json($orders, 200);
        } else {
            return response()->json(['message' => 'Order detail tidak ditemukan'], 404);
        }
    }
    
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
}
