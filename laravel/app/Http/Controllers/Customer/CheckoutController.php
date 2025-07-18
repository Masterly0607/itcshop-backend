<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        try {
            $customerId = $request->user()->id;

            // Get cart items
            if ($request->has('cart_id')) {
                $cartItem = Cart::with('product')
                    ->where('customer_id', $customerId)
                    ->where('id', $request->cart_id)
                    ->firstOrFail();
                $cartItems = collect([$cartItem]);
            } elseif ($request->boolean('checkout_all')) {
                $cartItems = Cart::with('product')
                    ->where('customer_id', $customerId)
                    ->get();

                if ($cartItems->isEmpty()) {
                    return response()->json(['message' => 'No items in cart'], 400);
                }
            } else {
                return response()->json(['message' => 'Missing cart_id or checkout_all flag'], 422);
            }

            DB::beginTransaction();

            $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

            // Auto apply coupon
// Auto apply coupon
$couponCode = $cartItems->firstWhere('coupon_code', '!=', null)?->coupon_code;
$discount = 0;
$coupon = null;

if ($couponCode) {
    $coupon = Coupon::where('code', $couponCode)
        ->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        })
        ->first();

    if ($coupon && ($coupon->usage_limit === null || $coupon->used < $coupon->usage_limit)) {
        if ($subtotal < $coupon->min_order_amount) {
            $coupon = null; // don't apply if not enough order value
        } else {
            $discount = $coupon->discount_type === 'fixed'
                ? min($coupon->discount_value, $subtotal)
                : $subtotal * ($coupon->discount_value / 100);
            $coupon->increment('used');
        }
    }
}


            $total = max($subtotal - $discount, 0);

            // Create order
            $order = Order::create([
                'customer_id' => $customerId,
                'total_price' => $total,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);
            }

            // Save a "pending" payment record
            Payment::create([
                'order_id' => $order->id,
                'amount' => $total,
                'status' => 'pending', // will update to 'succeeded' after stripe charge
                'type' => 'stripe',
                'created_by' => $customerId,
            ]);

            // Clear cart
            Cart::whereIn('id', $cartItems->pluck('id'))->delete();

            DB::commit();



            return response()->json([
                'message' => 'Checkout successful',
                'order_id' => $order->id,
                'subtotal' => $subtotal,
                'discount' => round($discount, 2),
                'total_price' => $total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
