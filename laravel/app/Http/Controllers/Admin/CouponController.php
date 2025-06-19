<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest ;
use App\Models\Coupon;


class CouponController extends Controller
{
    // Create coupon
    public function store(StoreCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return response()->json([
            'message' => 'Coupon created successfully.',
            'data' => $coupon,
        ], 201);
    }

    // Get all coupons
    public function index()
    {
        return Coupon::latest()->get();
    }

    // Get single coupon
    public function show(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    // Update coupon
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return response()->json([
            'message' => 'Coupon updated successfully.',
            'data' => $coupon,
        ]);
    }

    // Delete coupon
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully.',
        ]);
}
}
