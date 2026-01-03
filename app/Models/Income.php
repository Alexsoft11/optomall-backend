<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_group_id',
        'amount',
        'type',
        'original_price_yuan',
        'local_delivery_yuan',
        'profit_amount',
    ];

    protected $casts = [
        'original_price_yuan' => 'decimal:2',
        'local_delivery_yuan' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    public function orderGroup()
    {
        return $this->belongsTo(\App\Models\OrderGroup::class, 'order_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
