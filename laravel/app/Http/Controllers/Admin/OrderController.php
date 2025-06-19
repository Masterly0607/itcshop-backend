<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  public function index()
{
    return Order::with(['customer', 'items.product'])->latest()->get();
}

   public function show(Order $order)
{
    return $order->load(['customer', 'items.product']);
}

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $data['updated_by'] = auth()->id();

        $order->update($data);

        return response()->json([
            'message' => 'Order updated',
            'data' => $order,
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }
}
