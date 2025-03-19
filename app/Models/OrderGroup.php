<?php

namespace App\Models;

use App\Events\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGroup extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'status_color', 'total_price', 'user_id', 'is_paid', 'unique_id', 'delivery_china'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->unique_id = $model->generateUniqueId();
        });

        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                foreach ($order->orders as $orderr) {
                    event(new OrderStatusUpdated($orderr));
                }
            }
        });

        static::updated(function ($order) {
            if ($order->isDirty('is_paid')) {
                $order->orders()->update(['is_paid' => $order->is_paid]);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('status')) {
                $model->orders()->update(['status' => $model->status]);
            }
        });
    }

    protected function generateUniqueId()
    {
        $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));
        $numbers = substr(str_shuffle('0123456789'), 0, 4);
        return $letters . $numbers;
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

    protected static function booted(): void
    {
        // Delete all orders when orderGroup is deleted
        static::deleting(function (OrderGroup $orderGroup) {
            $orderGroup->orders()->delete();
        });
    }
}
