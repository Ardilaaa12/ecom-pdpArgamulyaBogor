<?php

namespace App\Http\Controllers\Api;

use App\Models\ShippingCost;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShippingCostController extends Controller
{
    public function index()
    {
        $data = ShippingCost::latest()->get();

        return new MasterResource(true, 'List data Shipping Cost', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city'  => 'required',
            'cost'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = ShippingCost::create([
            'city'  => $request->city,
            'cost'  => $request->cost,
        ]);

        if ($data) {
            return new MasterResource(true, 'Data Shipping Cost berhasil ditambahkan', $data);
        }
    }

    public function show($id)
    {
        $id = ShippingCost::find($id);

        return new MasterResource(true, 'Detail Shippinh Cost', $id);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'nullable',
            'cost' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = ShippingCost::find($id);

        $data->update([
            'city' => $request->city ?? $data->city,
            'cost' => $request->cost ?? $data->cost,
        ]);

        return new MasterResource(true, 'Biaya Pengiriman Berhasil diubah', $data);
    }

    public function destroy($id)
    {
        $id = ShippingCost::find($id);
        $id->delete();

        return new MasterResource(true, 'Biaya pengiriman dapat dihapus', null);
    }
}
