<?php

namespace App\Http\Resources\Customer;
use App\Http\Resources\Customer\OrderItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id'           => $this->id,
      'total_price'  => $this->total_price,
      'status'       => $this->status,
      'created_at'   => $this->created_at->toDateTimeString(),
      'items'        => OrderItemResource::collection($this->whenLoaded('items')),
    ];
  }
}
