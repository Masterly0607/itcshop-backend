<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'cart_id'  => 'nullable|integer',
            'checkout_all' => 'nullable|boolean',
        ]);

        $customerId = $request->user()->id;
        $code = $request->input('code');

        // Check if coupon exists
        $coupon = Coupon::where('code', $code)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon.'], 404);
        }

        // Get applicable cart items
        if ($request->has('cart_id')) {
            $cart = Cart::where('customer_id', $customerId)
                ->where('id', $request->cart_id)
                ->firstOrFail();

            $subtotal = $cart->product->price * $cart->quantity;

            // Check min order
            if ($subtotal < $coupon->min_order_amount) {
                return response()->json(['message' => 'Minimum order not met for this coupon.'], 400);
            }

            $cart->coupon_code = $code;
            $cart->save();
        } elseif ($request->boolean('checkout_all')) {
            $carts = Cart::with('product')
                ->where('customer_id', $customerId)
                ->get();

            if ($carts->isEmpty()) {
                return response()->json(['message' => 'Your cart is empty'], 400);
            }

            $subtotal = $carts->sum(fn($item) => $item->product->price * $item->quantity);

            if ($subtotal < $coupon->min_order_amount) {
                return response()->json(['message' => 'Minimum order not met for this coupon.'], 400);
            }

            Cart::where('customer_id', $customerId)->update([
                'coupon_code' => $code
            ]);
        } else {
            return response()->json(['message' => 'Missing cart_id or checkout_all flag'], 422);
        }

        return response()->json([
            'message' => 'Coupon applied successfully.',
            'code'    => $code,
        ]);
    }
}
