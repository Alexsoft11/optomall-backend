<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketItemColor extends Model
{
    protected $fillable = [
        'basket_id',
        'color_id',
    ];

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }
}
