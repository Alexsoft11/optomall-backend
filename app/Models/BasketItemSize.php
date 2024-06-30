<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketItemSize extends Model
{
    protected $fillable = [
        'basket_id',
        'size_id',
        'quantity'
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }
}
