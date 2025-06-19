<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_orders'    => Order::count(),
            'total_sales'     => Order::sum('total_price'),
            'total_customers' => Customer::count(),
            'total_products'  => Product::count(),

            'recent_orders' => Order::with('customer')
                ->latest()
                ->take(5)
                ->get(),

          'top_products' => \App\Models\Product::select('products.id', 'products.title')
    ->join('order_items', 'products.id', '=', 'order_items.product_id')
    ->selectRaw('SUM(order_items.quantity) as total_sold')
    ->groupBy('products.id', 'products.title')
    ->orderByDesc('total_sold')
    ->take(5)
    ->get(),

        ]);
    }
}
