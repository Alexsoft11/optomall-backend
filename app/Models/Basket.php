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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(BasketItemSize::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(BasketItemColor::class);
    }
}
