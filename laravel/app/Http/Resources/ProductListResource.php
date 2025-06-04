<?php

namespace App\Http\Resources;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Use ProductListResource for listing
        return
            [
                'id' => $this->id,
                'title' => $this->title,
                'slug' => $this->slug,
                'price' => $this->price,
                // ✅ Do this instead:
                'image' => url('storage/products/' . basename($this->image)),



                'created_at' => (new DateTime($this->created_at))->format('Y-m-d H:i:s'),
                'updated_at' => (new DateTime($this->updated_at))->format('Y-m-d H:i:s'),
            ];
    }
}
