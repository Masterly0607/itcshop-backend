<?php

namespace App\Http\Resources\Admin;

use DateTime;
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
        // Use ProductResource for product detail
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->image ? url($this->image) : null,
            'category_id' => $this->category_id,
            'is_flash_sale' => (bool) $this->is_flash_sale,
            'flash_sale_start' => $this->flash_sale_start,
            'flash_sale_end' => $this->flash_sale_end,
            'is_best_selling' => (bool) $this->is_best_selling,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
