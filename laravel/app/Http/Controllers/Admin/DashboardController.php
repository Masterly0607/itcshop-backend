<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total_price'),
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
        ]);
    }
}
