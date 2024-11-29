<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipping;
use App\Models\Order;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShippingControllers extends Controller
{
    // admin
    public function index()
    {
        $data = Order::with('shipping', 'user')->get();
        return new MasterResource(true, 'List Data Order dengan Shipping', $data);
    }
    


    public function store(Request $request)
    {
        // validasi
        $validator = Validator::make($request->all(), [
            'order_id'          => 'required|exists:orders,id',
            'shipping_address'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }

        $data = Shipping::create([
            'order_id'          => $request->order_id,
            'shipping_date'     => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'shipping_address'  => $request->shipping_address,
            'shipping_status'   => 'Persiapan Transport',
        ]);

        return new MasterResource(true, 'Data Pengiriman berhasil ditambahkan', $data);
    }

    public function show($id)
    {
        $data = Order::with('shipping', 'user')->find($id);

        return new MasterResource(true, "Detail Pengiriman", $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = Shipping::find($id);

        $data->update([
            'shipping_address' => $request->shipping_address,
        ]);

        return new MasterResource(true, 'Alamat Pengiriman berhasil diubah!', $data);
    }

    public function updateStatusPengiriman (string $id)
    {
        // Temukan shipping berdasarkan ID
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        $shipping = Shipping::where('order_id', $order->id)->first();        if (!$shipping) {
            return response()->json(['error' => 'Data Shipping tidak ditemukan!'], 404);
        }
        
        // Update status shipping
        $shipping->update(['shipping_status' => 'dalam perjalanan']);

        return response()->json(['message' => 'Status shipping berhasil diperbarui', 'status' => $shipping->shipping_status]);
    }

    public function updateStatusSampai (string $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        $shipping = Shipping::where('order_id', $order->id)->first();        if (!$shipping) {
            return response()->json(['error' => 'Data Shipping tidak ditemukan!'], 404);
        }

        // Update status shipping
        $shipping->update(['shipping_status' => 'sudah sampai']);

        return response()->json(['message' => 'Status shipping berhasil diperbarui', 'status' => $shipping->shipping_status]);
    }

    public function addShippingCost($id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_cost' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = Shipping::find($id);

        // $data->update([
        //     'shippinf_cost' => $request->shipping_cost;
        // ])
    }

    public function destroy($id)
    {
        $data = Shipping::find($id);
        $data->delete();

        return new MasterResource(true, 'Data Pengiriman berhasil di hapus!', null);
    }

    public function search(Request $request) 
    {
        $query = $request->input('query');
        
        // Periksa apakah query memiliki nilai sebelum dijalankan
        if (!$query) {
            return response()->json(['message' => 'Query tidak ditemukan'], 400);
        }

        $shipping = Shipping::where('shipping_date', 'LIKE', "%{$query}%")
            ->orWhere('shipping_address', 'LIKE', "%{$query}%")
            ->orWhere('shipping_status', 'LIKE', "%{$query}%")
            ->get();

        // Jika data tidak ditemukan, beri response yang sesuai
        if ($shipping->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($shipping);
    }

    public function status()
    {
        $shipping = Shipping::whereIn('status', ['disiapkan', 'dalam perjalanan'])
                            ->latest()
                            ->get();

        $shippingDone = Shipping::where('status', 'sudah sampai')
                            ->latest()
                            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Data',
            'pengiriman' => $shipping,
            'pengiriman_sampai' => $shippingDone,
        ]);
    }
}
