<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'customer_id',
        'items',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'status',
        'next_action',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'float',
    ];
}
