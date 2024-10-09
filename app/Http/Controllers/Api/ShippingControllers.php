<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipping;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ShippingControllers extends Controller
{
    public function index()
    {
        $data = Shipping::with('order')->get();
        return new MasterResource(true, 'List Data Pengiriman', $data);

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
        $data = Shipping::with('order')->find($id);

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
}
