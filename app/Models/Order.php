<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


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
        'kg'
    ];


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
