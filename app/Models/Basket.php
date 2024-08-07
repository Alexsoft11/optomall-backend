<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Basket extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'title',
        'price',
        'color',
        'quantity',
        'max_quantity',
        'size',
        'image_url',
        'seller_id',
        'provider',
        'skuId',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
