<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    // use HasSlug;

    protected $fillable = [
        'user_id',
        'delivery_time',
        'total_price',
        'status',
        'substatus',
        'status_color',
        'user_name',
        'user_phone',
        'location_url',
        'location_name',
        'region',
        'district',
        'street',
        'house',
        'post_index',
        'country',
        'substatus_uz',
        'substatus_ru',
        'substatus_en',
        'substatus_color',
        'substatus_slug',
        'kg',
        'unique_id',
        'seller_id',
        'provider',
        'order_group_id',
        'is_paid',
        'delivery_china',
    ];


    // public function getSlugOptions(): SlugOptions
    // {
    //     return SlugOptions::create()
    //         ->generateSlugsFrom('substatus_uz')
    //         ->saveSlugsTo('substatus_slug');
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->unique_id = $model->generateUniqueId();
        });

        static::updated(function ($order) {
            if ($order->isDirty('status') && $order->orderGroup) {
                $oldStatusId = $order->getOriginal('status');
                $newStatus = $order->getStatus()->first();
                $oldStatus = \App\Models\OrderStatus::find($oldStatusId);

                $wasCanceled = $oldStatus?->is_cancel;
                $isNowActive = !$newStatus?->is_cancel;

                if ($wasCanceled || !$isNowActive) {
                    $total = $order->orderGroup
                        ->orders()
                        ->whereHas('getStatus', function ($q) {
                            $q->where('is_cancel', false);
                        })
                        ->sum('total_price');
                    $order->orderGroup->update(['total_price' => $total]);
                }

                event(new \App\Events\OrderStatusUpdated($order));
            }
        });
    }




    public function generateUniqueId()
    {
        $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2));
        $numbers = substr(str_shuffle('0123456789'), 0, 6);
        return $letters . $numbers;
    }


    public function getStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status');
    }

    public function getSubStatus()
    {
        return $this->belongsTo(OrderSubStatus::class, 'substatus');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderGroup()
    {
        return $this->belongsTo(OrderGroup::class, 'order_group_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'order_id');
    }
}
