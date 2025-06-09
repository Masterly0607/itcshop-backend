<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        return Coupon::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string|unique:coupons,code',
            'type'         => 'required|in:fixed,percent',
            'value'        => 'required|numeric|min:0.01',
            'usage_limit'  => 'nullable|integer|min:1',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'is_active'    => 'boolean',
        ]);

        $coupon = Coupon::create($data);

        return response()->json([
            'message' => 'Coupon created successfully',
            'data'    => $coupon,
        ]);
    }

    public function show($id)
    {
        return Coupon::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'code'        => 'sometimes|required|string|unique:coupons,code,' . $id,
            'type'        => 'sometimes|required|in:fixed,percent',
            'value'       => 'sometimes|required|numeric|min:0.01',
            'usage_limit' => 'sometimes|nullable|integer|min:1',
            'start_date'  => 'sometimes|nullable|date',
            'end_date'    => 'sometimes|nullable|date|after_or_equal:start_date',
            'is_active'   => 'sometimes|boolean',
        ]);

        $coupon->update($data);

        return response()->json([
            'message' => 'Coupon updated successfully',
            'data'    => $coupon,
        ]);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully'
        ]);
    }
}
