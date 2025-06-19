<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;


class OrderController extends Controller
{
    // View all orders of the logged-in customer
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    // View a specific order by ID (only if it belongs to the customer)
    public function show(Request $request, $id)
    {
        $order = Order::with('items.product')
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
