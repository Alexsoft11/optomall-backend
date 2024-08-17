<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BasketItem extends Model
{
    protected $fillable = [
        'title',
        'price',
        'quantity',
        'max_quantity',
        'image_url',
        'product_id',
        'seller_id',
        'provider',
        'product_url',
        'skuId',
        'basket_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class, 'basket_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Properties::class);
    }
}
