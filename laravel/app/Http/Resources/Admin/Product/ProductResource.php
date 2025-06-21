<?php

namespace App\Http\Resources\Admin\Product;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // Resource file: used for specify the columns that we want to return and show in frontend.
    public function toArray(Request $request): array
    {
       return [
    'id' => $this->id,
    'title' => $this->title,
    'slug' => $this->slug,
    'description' => $this->description,
    'price' => $this->price,
    'images' => $this->images->map(fn ($img) => [
    'id' => $img->id,
    'image' => asset('storage/' . $img->image),
]),

    'category_id' => $this->category_id,
    'flash_sale_start' => $this->flash_sale_start,
    'flash_sale_end' => $this->flash_sale_end,
    'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
    'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
];

    }
}
