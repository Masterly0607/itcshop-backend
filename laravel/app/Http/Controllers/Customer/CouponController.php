<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $customerId = $request->user()->id;

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid or expired coupon'], 404);
        }

        if ($coupon->usage_limit !== null && $coupon->used >= $coupon->usage_limit) {
            return response()->json(['message' => 'Coupon usage limit reached'], 403);
        }

        $cartItems = Cart::with('product')
            ->where('customer_id', $customerId)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        $discount = 0;
        if ($coupon->type === 'fixed') {
            $discount = min($coupon->value, $subtotal); //  cap discount
        } elseif ($coupon->type === 'percent') {
            $discount = $subtotal * ($coupon->value / 100);
        }

        $total = max($subtotal - $discount, 0);

        // Save coupon_code to all cart rows for that customer
        Cart::where('customer_id', $customerId)->update([
            'coupon_code' => $coupon->code,
        ]);

        return response()->json([
            'message'  => 'Coupon applied successfully',
            'code'     => $coupon->code,
            'discount' => round($discount, 2),
            'total'    => round($total, 2),
        ]);
    }
}
