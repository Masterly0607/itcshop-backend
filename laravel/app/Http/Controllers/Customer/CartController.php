<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with('product')
            ->where('customer_id', $request->user()->id)
            ->get();

        return CartResource::collection($cart);
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $customerId = $request->user()->id;

        // 🔁 Find existing or create new cart item
        $cartItem = Cart::firstOrNew([
            'customer_id' => $customerId,
            'product_id'  => $request->product_id,
        ]);

        // ✅ Merge quantity instead of blocking
        $cartItem->quantity += $request->quantity;
        $cartItem->save();

        return response()->json([
            'message' => 'Product added to cart successfully',
            'data'    => new CartResource($cartItem),
        ]);
    }




    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart updated',
            'data' => new CartResource($cartItem),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Removed from cart']);
    }
}
