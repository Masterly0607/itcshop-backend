<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // View all orders
    public function index()
    {
        $orders = Order::with('customer', 'items.product')->latest()->get();
        return response()->json($orders);
    }

    // View single order
    public function show($id)
    {
        $order = Order::with('customer', 'items.product')->findOrFail($id);
        return response()->json($order);
    }

    // Update order status (processing, shipped, delivered, etc.)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order
        ]);
    }
}
