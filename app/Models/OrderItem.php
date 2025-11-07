<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_url',
        'product_id',
        'title',
        'image_url',
        'quantity',
        'price',
        'skuId',
        'provider',
        'delivery_price',
        'product_info'
    ];

    protected $casts = [
        'product_info' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Properties::class);
    }
}
