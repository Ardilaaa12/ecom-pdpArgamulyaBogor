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
     * Store a newly created resource in storage.
     */

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'order_id' => 'required',
    //         'payment_master_id' => 'required|exists:rekenings,id',
    //         'payment_date' => 'required',
    //         'payment_amount' => 'required|numeric',
    //         'payment_image' => 'required|image:png,jpg,jpeg,svg',
    //     ]);

    //     if($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $paymentImage = $request->file('payment_image');
    //     $paymentImageName = $paymentImage->hashName();
    //     $paymentImage->storeAs('public/payment', $paymentImageName);

    //     $paymentImageUrl = asset('storage/payment/' . $paymentImageName);

    //     $payment = Payment::create([
    //         'order_id' => $request->order_id,
    //         'payment_master_id' => $request->payment_master_id,
    //         'payment_date' => $request->payment_date,
    //         'payment_amount' => $request->payment_amount,
    //         'payment_image' => $paymentImageUrl,
    //     ]);

    //     if($payment) {
    //         return new MasterResource(true, 'Data user berhasil ditambahkan', $payment);
    //     } else {
    //      // Hapus file gambar jika penyimpanan data gagal
    //      Storage::delete('public/payment/' . $paymentImageName);
    //      return response()->json(['error' => 'Gagal menyimpan data payment'], 500);
    //     }
    // }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

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
            'payment_master_id' => 'nullable|exists:rekenings,id',
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

    public function search(Request $request) 
    {
        $query = $request->input('query');
        
        // Periksa apakah query memiliki nilai sebelum dijalankan
        if (!$query) {
            return response()->json(['message' => 'Query tidak ditemukan'], 400);
        }

        $payment = Payment::where('payment_date', 'LIKE', "%{$query}%")
            ->orWhere('payment_amount', 'LIKE', "%{$query}%")
            ->get();

        // Jika data tidak ditemukan, beri response yang sesuai
        if ($payment->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($payment);
    }
}
