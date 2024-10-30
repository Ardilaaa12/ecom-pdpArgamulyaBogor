<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{

    // admin 
    public function index()
    {
        $user = User::with(['order'])->get();
        // $order = Order::with(['user'])->get();
        return new MasterResource(true, 'List data yang ada di order', $user);
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
        $validator = $request->validate([
            'user_id' => 'required|exists:users,id',
            
        ]);
    }

    // admin
    public function show(string $id)
    {
        $order = Order::find($id);

        if ($order) {
            return new MasterResource(true, 'Detail data order', $order);
        } else {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function search(Request $request) 
    {
        $query = $request->input('query');
        
        // Periksa apakah query memiliki nilai sebelum dijalankan
        if (!$query) {
            return response()->json(['message' => 'Query tidak ditemukan'], 400);
        }

        $order = Order::where('no_ref_order', 'LIKE', "%{$query}%")
            ->orWhere('total_amount', 'LIKE', "%{$query}%")
            ->orWhere('order_date', 'LIKE', "%{$query}%")
            ->orWhere('status', 'LIKE', "%{$query}%")
            ->get();

        // Jika data tidak ditemukan, beri response yang sesuai
        if ($order->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($order);
    }

    public function monthlyData(Request $request)
    {
        $Bulan = Carbon::now()->month;
        $Tahun = Carbon::now()->year;

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $penjualanBulanan= Order::whereIn('orders.status', ['verifikasi pembayaran', 'berhasil'])
                    ->whereMonth('orders.created_at', $Bulan)
                    ->whereYear('orders.created_at', $Tahun)
                    ->join('payments', 'orders.id', '=', 'payments.order_id')
                    ->selectRaw('SUM(payments.payment_amount) as total_penjualan_bulanan')
                    ->first();

        $penghasilanBulanan = 'Rp ' . number_format($penjualanBulanan->total_penjualan_bulanan ?? 0, 2, ',', '.');
        
        $hasil = [
            'total sales' => $penghasilanBulanan,
            'bulan'       => $bulan[$Bulan],
            'tahun'       => $Tahun,
        ];

        $penjualanTahunan = Order::whereIn('status', ['verifikasi pembayaran', 'berhasil'])
                            ->whereYear('orders.created_at', $Tahun) // Filter berdasarkan tahun saat ini
                            ->join('payments', 'orders.id', '=', 'payments.order_id')
                            ->selectRaw('SUM(payments.payment_amount) as total_penjualan_tahunan, YEAR(orders.created_at) as year')
                            ->groupBy('year')
                            ->get();
    
        $totalPenjualanTahunan = $penjualanTahunan->sum('total_penjualan_tahunan');
        $penghasilanTahunan = 'Rp ' . number_format($totalPenjualanTahunan, 2, ',', '.');

        return response()->json([
            'Penjualan tahunan ini' =>$penghasilanTahunan,
            'Penjualan Bulan ini' => $hasil,
        ]);
    }
}
