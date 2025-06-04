<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search', false);
        $perPage = request('per_page', 10);
        $query = Product::query();
        $sortField = request('sort_field', 'updated_at');
        $sortDirection = request('sort_direction', 'desc');
        $query->OrderBy($sortField, $sortDirection);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return ProductListResource::collection($query->paginate($perPage));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $relativePath = $image->store('products', 'public');
            $data['image'] = "/storage/{$relativePath}";

            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
        }

        $product = Product::create($data);

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        // Update logic: safe image upload just like in store()
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $relativePath = $image->store('products', 'public');
            $data['image'] = "/storage/{$relativePath}";

            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
        }

        $product->update($data);

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response([
            'message' => 'You deleted an record!'
        ]);
    }

    // Restore soft deleted product by id
    public function restore($id)
    {
        $product = Product::withTrashed()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->deleted_at === null) {
            return response()->json(['message' => 'Product is not deleted'], 400);
        }

        $product->restore();

        return response()->json(['message' => 'Product restored successfully']);
    }
}
