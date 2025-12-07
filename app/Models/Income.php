<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_group_id', 'amount', 'type'];

    public function orderGroup()
    {
        return $this->belongsTo(\App\Models\OrderGroup::class, 'order_group_id');
    }
}
