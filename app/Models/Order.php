<?php

namespace App\Models;

use App\Events\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Order extends Model
{
    use HasFactory;
    // use HasSlug;

    protected $fillable = [
        'user_id',
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
        'kg'
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

        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                event(new OrderStatusUpdated($order));
            }
        });
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

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function checks()
    {
        return $this->hasMany(OrderCheck::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'order_id');
    }
}
