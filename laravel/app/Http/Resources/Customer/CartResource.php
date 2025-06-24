<?php
namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{

    public function toArray(Request $request): array
   
    {
       $firstImage = $this->product?->images->first()?->image;

        return [
            'id'       => $this->id,
            'quantity' => $this->quantity,
            'price'    => $this->product->price,
            'title'    => $this->product->title,
            'image'    => $firstImage ? asset('storage/' . $firstImage) : null,
        ];
    }
}