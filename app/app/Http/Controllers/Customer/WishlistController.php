<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = Wishlist::with('product')->where('customer_id', $request->user()->id)->get();
        return response()->json($wishlist);
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $exists = Wishlist::where('customer_id', $request->user()->id)
            ->where('product_id', $request->product_id)->exists();

        if ($exists) {
            return response()->json(['message' => 'Already in wishlist'], 409);
        }

        $item = Wishlist::create([
            'customer_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Added to wishlist', 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = Wishlist::where('id', $id)->where('customer_id', $request->user()->id)->firstOrFail();
        $item->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
