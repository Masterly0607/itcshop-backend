<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCard extends Model
{
    protected $fillable = [
        'customer_id',
        'stripe_card_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
