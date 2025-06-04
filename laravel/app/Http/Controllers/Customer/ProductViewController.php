<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Http\Resources\Customer\ProductResource;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductViewController extends Controller
{
    // View all products (with optional search and category filter)
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($search = $request->query('search')) {
            $query->where('title', 'LIKE', "%$search%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12);

        return ProductResource::collection($products);
    }

    // Flash sale products
    public function flashSale()
    {
        $today = Carbon::today();

        $products = Product::where('is_flash_sale', true)
            ->whereDate('flash_sale_start', '<=', $today)
            ->whereDate('flash_sale_end', '>=', $today)
            ->with('category')
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }

    // Best selling products
    public function bestSelling()
    {
        $products = Product::where('is_best_selling', true)
            ->with('category')
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }

    // New products
    public function newProducts()
    {
        $products = Product::where('is_new', true)
            ->with('category')
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }

    // Products by category name (supports search as query param)
    public function byCategoryName(Request $request, $categoryName)
    {
        $category = Category::where('slug', Str::slug($categoryName))->firstOrFail();

        $query = Product::where('category_id', $category->id)
            ->with('category');

        if ($search = $request->query('search')) {
            $query->where('title', 'LIKE', "%$search%");
        }

        $products = $query->latest()->paginate(12);

        return ProductResource::collection($products);
    }
}
