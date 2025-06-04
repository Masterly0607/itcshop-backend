<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'total_price',
        'status',
        'created_by',
        'updated_by',
    ];
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
