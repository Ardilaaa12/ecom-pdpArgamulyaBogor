<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Shipping;
use App\Models\Payment;
use App\Models\Rekening;
use App\Models\Category;
use App\Models\Section;
use App\Models\Content;
use App\Models\Navbar;
use App\Exports\SalesReportExport;
use App\Exports\SheepReportExport;
use App\Exports\PaymentReportExport;
use App\Exports\ShippingReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GlobalController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('search'); // Ambil query dari URL
        $results = [];

        // Pencarian di setiap tabel
        if (!empty($query)) {
            $results['orders'] = Order::where('no_ref_order', 'LIKE', "%{$query}%")
                                    ->orWhere('total_amount', 'LIKE', "%{$query}%")
                                    ->orWhere('order_date', 'LIKE', "%{$query}%")
                                    ->orWhere('status', 'LIKE', "%{$query}%")
                                    ->get();

            $results['products'] = Product::where('name_product', 'LIKE', "%{$query}%")
                                    ->orWhere('description', 'LIKE', "%{$query}%")
                                    ->orWhere('age', 'LIKE', "%{$query}%")
                                    ->orWhere('weight', 'LIKE', "%{$query}%")
                                    ->orWhere('price', $query)
                                    ->get();

            $results['users'] = User::where('username', 'LIKE', "%{$query}%")
                                    ->orWhere('fullname', 'LIKE', "%{$query}%")
                                    ->orWhere('email', 'LIKE', "%{$query}%")
                                    ->orWhere('address', 'LIKE', "%{$query}%")
                                    ->orWhere('phone_number', 'LIKE', "%{$query}%")
                                    ->get();

            $results['shippings'] = Shipping::where('shipping_date', 'LIKE', "%{$query}%")
                                    ->orWhere('shipping_address', 'LIKE', "%{$query}%")
                                    ->orWhere('shipping_status', 'LIKE', "%{$query}%")
                                    ->get();

            $results['payments'] = Payment::where('payment_date', 'LIKE', "%{$query}%")
                                    ->orWhere('payment_amount', 'LIKE', "%{$query}%")
                                    ->orWhere('account_name', 'LIKE', "%{$query}%")
                                    ->get();

            $results['rekening'] = Rekening::where('payment_method', 'LIKE', "%{$query}%")
                                    ->get();

            $results['categories'] = Category::where('name_category', 'LIKE', "%{$query}%")
                                    ->get();

            $results['sections'] = Section::where('title', 'LIKE', "%{$query}%")
                                    ->orWhere('description', 'LIKE', "%{$query}%")
                                    ->orWhere('status', 'LIKE', "%{$query}%")
                                    ->orWhere('type', 'LIKE', "%{$query}%")
                                    ->get();

            $results['content'] = Content::where('title', 'LIKE', "%{$query}%")
                                    ->orWhere('description', 'LIKE', "%{$query}%")
                                    ->orWhere('status', 'LIKE', "%{$query}%")
                                    ->orWhere('type', 'LIKE', "%{$query}%")
                                    ->get();

            $results['navbars'] = Navbar::where('name', 'LIKE', "%{$query}%")
                                    ->orWhere('route', 'LIKE', "%{$query}%")
                                    ->orWhere('type', 'LIKE', "%{$query}%")
                                    ->orWhere('status', $query)
                                    ->get();
        }
        return new MasterResource(true, 'Hasil data search', $results);
    }

    public function exportSalesReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $orders = Order::with('user')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
        ->get();

        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'no_ref_order'  => $order->no_ref_order,
                'created_at'    => $order->created_at ? $order->created_at->format('d-M-y') : '-',
                'user_name'     => $order->user ? $order->user->fullname : '-',
                'total_amount'  => $order->total_amount,
                'status'        => $order->status,
            ];
        }
    
        return Excel::download(new SalesReportExport($data), 'sales_report_' . now()->format('Ymd_His') . '.xlsx');
    }    

    public function exportSheepStockReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $products = Product::with('category')
        ->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])
        ->get();

        $data = [];
        foreach ($products as $product) {
            $stockStatus = $product->stock > 0 ? 'Tersedia' : 'Habis';

            $data[] = [
                'id'            => $product->id,
                'name_product'  => $product->name_product,
                'name_category' => $product->category->name_category,
                'age'           => $product->age ? $product->age : '-',
                'weight'        => $product->weight ? $product->weight : '-',
                'price'         => $product->price,
                'stock'         => $product->stock,
                'status'        => $stockStatus,
            ];
        }

        return Excel::download(new SheepReportExport($data),'sheep_stock_report_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPaymentReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $payments = Payment::with('order', 'rekening')
        ->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->get();

        $data = [];
        foreach ($payments as $payment) {
            $status = $payment->payment_amount == $payment->order->total_amount ? 'Lunas' : 'Belum Lunas';

            $data[] = [
                'no_ref_order' => $payment->order->no_ref_order,
                'id_payment' => $payment->id,
                'updated_at' => $payment->updated_at,
                'payment_method' => $payment->rekening->payment_method,
                'payment_amount' => $payment->payment_amount,
                'account_name' => $payment->account_name,
                'status'         => $status
            ];
        }

        return Excel::download(new PaymentReportExport($data), 'payment_report_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportShippingReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $shipping = Shipping::with('order.user')
        ->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->get();

        $data = [];
        foreach ($shipping as $ship) {
            $data[] = [
                'no_ref_order' => $ship->order->no_ref_order,
                'id_shipping' => $ship->id,
                'shipping_date' => $ship->shipping_date,
                'fullname' => $ship->order->user->fullname,
                'shipping_address' => $ship->shipping_address,
                'shipping_status' => $ship->shipping_status,
            ];
        }
        return Excel::download(new ShippingReportExport($data), 'shipping_report_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function SalesReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $orders = Order::with('user')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
        ->get();

        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'no_ref_order'  => $order->no_ref_order,
                'created_at'    => $order->created_at ? $order->created_at->format('d-M-y') : '-',
                'user_name'     => $order->user ? $order->user->fullname : '-',
                'total_amount'  => $order->total_amount,
                'status'        => $order->status,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan',
            'data'    => $data
        ]);    
    }

    public function sheepStockReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $products = Product::with('category')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
        ->get();

        $data = [];
        foreach ($products as $product) {
            $stockStatus = $product->stock > 0 ? 'Tersedia' : 'Habis';

            $data[] = [
                'id'            => $product->id,
                'name_product'  => $product->name_product,
                'name_category' => $product->category->name_category,
                'age'           => $product->age ? $product->age : '-',
                'weight'        => $product->weight ? $product->weight : '-',
                'price'         => $product->price,
                'stock'         => $product->stock,
                'status'        => $stockStatus,
            ];
        }

        // Mengembalikan data dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Data stok domba',
            'data'    => $data
        ]);
    }

    public function paymentReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $payments = Payment::with('order', 'rekening')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
        ->get();

        $data = [];
        foreach ($payments as $payment) {
            $status = $payment->payment_amount == $payment->order->total_amount ? 'Lunas' : 'Belum Lunas';
            $paymentMethod = $payment->rekening ? $payment->rekening->payment_method : '-';

            $data[] = [
                'no_ref_order' => $payment->order->no_ref_order,
                'id_payment' => $payment->id,
                'updated_at' => $payment->updated_at,
                'payment_method' => $payment->rekening->payment_method,
                'payment_amount' => $payment->payment_amount,
                'account_name' => $payment->account_name,
                'status'         => $status
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Pembayaran klien',
            'data'    => $data
        ]);    
    }

    public function ShippingReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $shipping = Shipping::with('order.user')
        ->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->get();

        $data = [];
        foreach ($shipping as $ship) {
            $data[] = [
                'no_ref_order' => $ship->order->no_ref_order,
                'id_shipping' => $ship->id,
                'shipping_date' => $ship->shipping_date,
                'fullname' => $ship->order->user->fullname,
                'shipping_address' => $ship->shipping_address,
                'shipping_status' => $ship->shipping_status,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Pengiriman domba',
            'data'    => $data
        ]);    
    }
}
