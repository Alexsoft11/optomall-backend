<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'color_id',
        'size_id',
        'composition_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
