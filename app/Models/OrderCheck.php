<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_group_id',
        'image',
        'card_number_id'
    ];

    public function order()
    {
        return $this->belongsTo(OrderGroup::class, 'order_group_id');
    }
}
