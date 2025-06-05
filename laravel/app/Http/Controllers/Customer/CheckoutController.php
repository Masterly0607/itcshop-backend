<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan; // add this at the top if not already
class CheckoutController extends Controller
{


    public function store(Request $request)
    {
        try {
            $customerId = $request->user()->id;

            // Checkout single item
            if ($request->has('cart_id')) {
                $cartItem = Cart::with('product')
                    ->where('customer_id', $customerId)
                    ->where('id', $request->cart_id)
                    ->firstOrFail();

                $cartItems = collect([$cartItem]); // wrap in collection
            }
            // Checkout all
            elseif ($request->boolean('checkout_all')) {
                $cartItems = Cart::with('product')
                    ->where('customer_id', $customerId)
                    ->get();

                if ($cartItems->isEmpty()) {
                    return response()->json([
                        'message' => 'No items in cart',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Missing cart_id or checkout_all flag',
                ], 422);
            }

            // Begin transaction
            DB::beginTransaction();

            $total = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $order = Order::create([
                'customer_id' => $customerId,
                'total_price' => $total,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->product->price,
                    'subtotal'   => $item->product->price * $item->quantity,
                ]);
            }

            // Remove checked-out items
            Cart::whereIn('id', $cartItems->pluck('id'))->delete();

            DB::commit();

            // Update flags after successful order
            Artisan::call('products:update-flags');

            return response()->json([
                'message'  => 'Checkout successful',
                'order_id' => $order->id,
                'total'    => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Checkout failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
