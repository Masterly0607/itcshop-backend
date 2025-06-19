<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ProductDetailResource;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Http\Resources\Customer\ProductResource;

class ProductViewController extends Controller
{
    // View all products (with optional search and category filter)
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($search = $request->query('search')) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $search));
            $query->whereRaw("REPLACE(REPLACE(LOWER(title), ' ', ''), '-', '') LIKE ?", ["%{$normalized}%"]);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12);
        return ProductResource::collection($products);
    }

    // Flash sale products (based on date)
    public function flashSale()
    {
        $now = now()->toDateString(); // only keep date, e.g. "2025-06-09"

        $products = Product::whereDate('flash_sale_start', '<=', $now)
            ->whereDate('flash_sale_end', '>=', $now)
            ->with('category')
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }

    // Best selling products (based on order_items)
    public function bestSelling()
    {
        $bestSellingIds = OrderItem::select('product_id')
            ->groupBy('product_id')
            ->orderByRaw('SUM(quantity) DESC')
            ->limit(10)
            ->pluck('product_id');

        $products = Product::whereIn('id', $bestSellingIds)
            ->with('category')
            ->get();

        return ProductResource::collection($products);
    }

    // New products (within last 7 days = From 7 days ago up to today)
    public function newProducts()
    {
        $products = Product::where('created_at', '>=', now()->subDays(7))
            ->with('category')
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }

    // ✅ Product detail by ID
    public function show($id)
    {
        $product = Product::with(['category', 'images'])->findOrFail($id);
        return new ProductDetailResource($product);
    }
}
