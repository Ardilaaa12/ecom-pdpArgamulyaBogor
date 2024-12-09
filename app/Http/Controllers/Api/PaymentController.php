<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with('rekening')->get();
        
        // Kembalikan data pembayaran dan rekening terkait
        return response()->json($payments);
    }

    public function generateInvoice($orderId)
    {
        // Mengambil data order beserta relasinya
        $data = Order::with('user', 'orderDetail.product.category', 'payment.rekening')
            ->where('id', $orderId)
            ->first();

        if (!$data) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Load view dan passing data
        $pdf = Pdf::loadView('invoice', [
            'order' => $data,
            'customer' => $data->user,
            'payment' => $data->payment,
        ]);

        // Mengunduh file PDF
        $pdf->setPaper('a5', 'landscape');
        $fileName = 'Invoice_Order_' . $orderId . '.pdf';
        return $pdf->download($fileName);
    }


    public function show($id)
    {
        $data = Order::with('user', 'orderDetail.product.category', 'payment.rekening')
            ->where('id', $id)
            ->first();

        if (!$data) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // $pdf = Pdf::loadView('invoice', $data)->setPaper('a5', 'landscape');
        // return $pdf->download('invoice.pdf');

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
            'payment_date' => 'required',
            'payment_amount' => 'required|numeric',
            'payment_image' => 'required|image|mimes:png,jpg,jpeg,svg',
            'account_name' => 'nullable|regex:/^[A-Za-z\s]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = Payment::find($id);

        if ($request->hasFile('payment_image')) {
            $paymentImage = $request->file('payment_image');
            $paymentImageName = $paymentImage->hashName();
            $paymentImage->storeAs('public/payment', $paymentImageName);
            $paymentImageUrl = asset('storage/payment/' . $paymentImageName);

            Storage::delete('public/payment/' . basename($payment->payment_image));

            $payment->update([
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
                'payment_image' => $paymentImageUrl,
                'account_name' => $request->account_name,
            ]);

            if ($request->filled('payment_master_id')) {
                $payment->update(['payment_master_id' => $request->payment_master_id]);
            }

        } else {
            $data = [
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
                'account_name' => $request->account_name,
            ];

            if ($request->filled('payment_master_id')) {
                $data['payment_master_id'] = $request->payment_master_id;
            }

            $payment->update($data);
        }

        $order = Order::find($payment->order_id);
        if ($order) {
            $order->update(['status' => 'verifikasi pembayaran']);
        }

        return new MasterResource(true, 'Berhasil mengubah data payment', $payment);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        Storage::delete('public/payment/' . basename($payment->payment_image));
        $payment->delete();

        return new MasterResource(true, 'data payment berhasil di hapus', null);
    }
}
