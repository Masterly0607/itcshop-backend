<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'description'    => $this->description,
    'images' => $this->whenLoaded('images', function () {
    return $this->images->map(fn($img) => asset('storage/' . $img->image));
}, []),


            'price'          => number_format($this->price, 2),
            'oldPrice'       => $this->old_price ? number_format($this->old_price, 2) : null,
            'rating'         => $this->rating ?? 4,
            'stock'          => $this->stock ?? 10,
            'category'       => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),

        
            'created_at'      => $this->created_at->toDateTimeString(),
        ];
    }
}
