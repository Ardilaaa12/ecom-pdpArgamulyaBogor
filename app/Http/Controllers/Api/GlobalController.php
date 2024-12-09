<?php

namespace App\Http\Controllers\Api;

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
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return response()->json($results);
    }
}
