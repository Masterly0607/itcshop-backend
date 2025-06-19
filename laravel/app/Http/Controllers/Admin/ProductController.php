<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\Customer\ProductDetailResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
{
    $products = Product::with(['images', 'category'])->latest()->get();
    return ProductResource::collection($products);
}
 public function show(Product $product)
    {
        return new ProductDetailResource($product->load(['images', 'category']));
    }

   public function store(StoreProductRequest $request)
{
    $data = $request->validated();

    // Add admin who created
    $data['created_by'] = auth()->id();

    // Create product
    $product = Product::create($data);

    // Store multiple images if present
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
          $product->images()->create([
    'image' => $image->store('products', 'public'),
    'image_mime' => $image->getClientMimeType(),
    'image_size' => $image->getSize(),
]);

        }
    }

    return new ProductResource($product);
}


public function update(UpdateProductRequest $request, Product $product)
{
    $data = $request->validated();

    $data['updated_by'] = auth()->id();

    // Update product basic info
    $product->update($data);

    // If new images uploaded → delete old images and save new ones
    if ($request->hasFile('images')) {
        // Delete old images from storage and DB
        foreach ($product->images as $oldImg) {
            Storage::disk('public')->delete($oldImg->url);
            $oldImg->delete();
        }

        // Add new images
        foreach ($request->file('images') as $image) {
           $product->images()->create([
    'image' => $image->store('products', 'public'),
    'image_mime' => $image->getClientMimeType(),
    'image_size' => $image->getSize(),
]);

        }
    }

    return new ProductResource($product);
}
public function destroy(Product $product)
{
    $product->delete(); // soft delete
    return response()->json([
        'message' => 'Product deleted successfully.'
    ]);
}
}
