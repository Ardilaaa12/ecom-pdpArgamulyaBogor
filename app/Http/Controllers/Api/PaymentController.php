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

    // buat pdf struk
    public function generateInvoice($orderId)
    {
        // Mengambil data order beserta relasinya
        $data = Order::with('user', 'orderDetail.product.category', 'payment.rekening', 'shipping.shippingCost')
            ->where('id', $orderId)
            ->first();

        if (!$data) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $total = $data->total_amount - $data->shipping->shippingCost->cost;

        // Load view dan passing data
        $pdf = Pdf::loadView('invoice', [
            'order' => $data,
            'customer' => $data->user,
            'payment' => $data->payment,
            'shipping' => $data->shipping->shippingCost,
            'total' => $total,
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

        // Cari Order berdasarkan ID
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        // Cari Payment berdasarkan order_id
        $payment = Payment::where('order_id', $order->id)->first();

        // Jika payment_amount tidak null, buat data baru
        if ($payment && $payment->payment_amount !== null) {
            // Tambahkan data baru ke tabel Payment
            $newPayment = new Payment([
                'order_id' => $order->id,
                'payment_date' => $request->payment_date,
                'payment_amount' => $request->payment_amount,
                'payment_image' => $request->file('payment_image')->storeAs(
                    'public/payment',
                    $request->file('payment_image')->hashName()
                ),
                'account_name' => $request->account_name,
                'payment_master_id' => $payment ? $payment->payment_master_id : 0,
            ]);
            $newPayment->save();

            // Perbarui status Order
            $order->update(['status' => 'verifikasi pembayaran']);

            return new MasterResource(true, 'Berhasil menambahkan data payment baru', $newPayment);
        }

        // Jika payment_amount null atau data payment belum ada, update data
        if ($payment) {
            if ($request->hasFile('payment_image')) {
                $paymentImage = $request->file('payment_image');
                $paymentImageName = $paymentImage->hashName();
                $paymentImage->storeAs('public/payment', $paymentImageName);

                // Hapus gambar lama
                if ($payment->payment_image) {
                    Storage::delete('public/payment/' . basename($payment->payment_image));
                }

                $payment->update([
                    'payment_date' => $request->payment_date,
                    'payment_amount' => $request->payment_amount,
                    'payment_image' => '/storage/payment/' . $paymentImageName,
                    'account_name' => $request->account_name,
                ]);

            } else {
                $data = [
                    'payment_date' => $request->payment_date,
                    'payment_amount' => $request->payment_amount,
                    'account_name' => $request->account_name,
                ];
                $payment->update($data);
            }
        }
        // Perbarui status Order
        $order->update(['status' => 'verifikasi pembayaran']);

        return new MasterResource(true, 'Berhasil mengubah data payment', $payment);
    }

}
