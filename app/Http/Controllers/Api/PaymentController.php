<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payment = Payment::with(['order'], ['rekening'])->get();
        return new MasterResource(true, 'List data yang ada di payment', $payment);
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
            'order_id' => 'required',
            'payment_master_id' => 'required|exists:rekenings,id',
            'payment_date' => 'required',
            'payment_amount' => 'required|numeric',
            'payment_image' => 'required|image:png,jpg,jpeg,svg',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentImage = $request->file('payment_image');
        $path = $paymentImage->storeAs('public/paymentImage', $paymentImage->hashName());

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'payment_master_id' => $request->payment_master_id,
            'payment_date' => $request->payment_date,
            'payment_amount' => $request->payment_amount,
            'payment_image' => $paymentImage->hashName(),
        ]);

        if($payment) {
            return new MasterResource(true, 'Data user berhasil ditambahkan', $payment);
        } else {
            // hapus gambar jika penyimpanan data gagal 
            Storage::delete($path);
            return response()->json(['error' => 'Gagal menyimpan data user'], 500);
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
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'payment_master_id' => 'required|exists:rekenings,id',
            'payment_date' => 'required',
            'payment_amount' => 'required|numeric',
            'payment_image' => 'required|image:png,jpg,jpeg,svg',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = Payment::find($id);

        if($request->hasFile('payment_image')) {
            // upload image
            $paymentImage = $request->file('payment_image');
            $paymentImage->storeAs('public/paymentImage', $paymentImage->hashName());
            // delete old image
            Storage::delete('public/paymentImage/' . basename($payment->payment_image));
            // upload payment with new image 
            $payment->update([
                'order_id' => $request->order_id,
                'payment_master_id' => $request->payment_master_id,
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
                'payment_image' => $paymentImage->hashName(),
            ]);
        } else {
            $payment->update([
                'order_id' => $request->order_id,
                'payment_master_id' => $request->payment_master_id,
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
            ]);
        }

        return new MasterResource(true, 'Berhasil mengubah data payment', $payment);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        Storage::delete('public/paymentImage/' . basename($payment->payment_image));
        $payment->delete();

        return new MasterResource(true, 'data payment berhasil di hapus', null);
    }
}
