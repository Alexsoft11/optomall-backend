<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'value',
        'order_item_id',
        'basket_item_id',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function basketItem()
    {
        return $this->belongsTo(BasketItem::class, 'basket_item_id');
    }

    public function getNameTooltip(): string
    {
        return $this->pluck('name')->implode(', ') ?: 'Default Tooltip';
    }

    public function getValueTooltip(): string
    {
        return $this->pluck('value')->implode(', ') ?: 'Default Tooltip';
    }
}
