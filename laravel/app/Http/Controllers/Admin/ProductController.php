<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\Admin\Product\ProductResource;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
public function index()
{
    $search = request('search');
    $perPage = request('per_page', 10);

    //  Secure & safe fallback
    $sortField = in_array(request('sort_field'), ['title', 'price', 'updated_at']) 
        ? request('sort_field') 
        : 'updated_at';

    $sortDirection = in_array(request('sort_direction'), ['asc', 'desc']) 
        ? request('sort_direction') 
        : 'desc';

    $query = Product::with(['images', 'category'])
        ->orderBy($sortField, $sortDirection);

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    return ProductResource::collection($query->paginate($perPage));
}



 public function show(Product $product)
    {
return new ProductResource($product->load(['images', 'category']));

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

  $product->update([
    'title' => $data['title'] ?? $product->title,
    'description' => $data['description'] ?? $product->description,
    'price' => $data['price'] ?? $product->price,
    'category_id' => $data['category_id'] ?? $product->category_id,
    'flash_sale_start' => $data['flash_sale_start'] ?? $product->flash_sale_start,
    'flash_sale_end' => $data['flash_sale_end'] ?? $product->flash_sale_end,
]);

 // 🔍 Log removed_image_ids to debug
    Log::info('🧹 Remove image IDs:', [
        'raw' => $request->input('removed_image_ids')
    ]);
    // Handle deleted images
   $ids = $request->input('removed_image_ids');
if (!empty($ids)) {
    $ids = is_string($ids) ? json_decode($ids, true) : $ids;

    $imagesToDelete = $product->images()->whereIn('id', $ids)->get();

    foreach ($imagesToDelete as $img) {
        Storage::disk('public')->delete($img->image);
        $img->delete();
    }
}


    // Handle newly uploaded images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $img) {
            $product->images()->create([
                'image' => $img->store('products', 'public'),
                'image_mime' => $img->getClientMimeType(),
                'image_size' => $img->getSize(),
            ]);
        }
    }

    return response()->json([
        'message' => 'Product updated successfully.',
      'data' => new ProductResource($product->load(['images', 'category'])),

    ]);
}



public function destroy(Product $product)
{
    $product->delete(); // soft delete
    return response()->json([
        'message' => 'Product deleted successfully.'
    ]);
}
}
