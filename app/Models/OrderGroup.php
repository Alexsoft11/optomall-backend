<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGroup extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'status_color', 'total_price', 'user_id', 'is_paid'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function checks()
    {
        return $this->hasMany(OrderCheck::class);
    }

    public function getStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status');
    }
}
