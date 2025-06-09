<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'amount', 'status', 'type', 'created_by', 'updated_by'];
}
