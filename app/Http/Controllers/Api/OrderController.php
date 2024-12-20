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
        $user = User::whereHas('order', function ($query) {
            $query->where('status', 'verifikasi pembayaran');
        })
        ->with(['order' => function ($query) {
            $query->where('status', 'verifikasi pembayaran');
        }, 'order.shipping'])
        ->get();

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
        $order = Order::with(['orderDetail', 'shipping'])->find($id);

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

    public function status()
    {
        $order = Order::whereIn('status', ['verifikasi pengiriman', 'menunggu pembayaran', 'verifikasi pembayaran'])
                                    ->with(['user'])
                                    ->latest()
                                    ->get();

        return new MasterResource(true, 'Data Order', $order);
    }

    public function statusBerhasil()
    {
        $order = Order::where('status', 'berhasil')
                                    ->with(['user'])
                                    ->latest()
                                    ->get();

        return response()->json(new MasterResource(true, 'Data Order Berhasil', $order))
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    public function statusGagal()
    {
        $order = Order::where('status', 'gagal')
                                    ->with(['user'])
                                    ->latest()
                                    ->get();

        return new MasterResource(true, 'Data Order Gagal', $order);
    }
}
