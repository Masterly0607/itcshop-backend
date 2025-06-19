<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'product_id' => $this->product_id,
      'quantity'   => $this->quantity,
      'unit_price' => $this->unit_price,
      'subtotal'   => $this->subtotal,
      'product'    => [
        'id'    => $this->product->id,
        'title' => $this->product->title,
        'image' => $this->product->image,
        'price' => $this->product->price,
      ],
    ];
  }
}
