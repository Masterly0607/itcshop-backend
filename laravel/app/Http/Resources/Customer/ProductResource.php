<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   */
  public function toArray(Request $request): array
  {
    $firstImage = $this->images->first()?->image;
    return [
      'id'          => $this->id,
      'title'       => $this->title,
      'slug'        => $this->slug,
    'image' => $firstImage ? asset('storage/' . $firstImage) : null,

      'description' => $this->description,
      'price'       => $this->price,
      'category'    => $this->whenLoaded('category', function () {
        return [
          'id'   => $this->category->id,
          'name' => $this->category->name,
        ];
      }),
      'created_at'  => $this->created_at,
    ];
  }
}
