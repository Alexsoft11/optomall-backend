<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Order extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'status_color',
        'user_name',
        'user_phone',
        'location_url',
        'location_name',
        'country',
        'substatus_uz',
        'substatus_ru',
        'substatus_en',
        'substatus_color',
        'substatus_slug',
        'kg'
    ];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('substatus_uz')
            ->saveSlugsTo('substatus_slug');
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


    protected $appends = ['substatus'];

    public function getSubstatusAttribute()
    {
        return [
            'uz' => $this->substatus_uz,
            'ru' => $this->substatus_ru,
            'en' => $this->substatus_en,
        ];
    }
}
