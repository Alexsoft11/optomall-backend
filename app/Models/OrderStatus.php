<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class OrderStatus extends BaseModel
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'name_uz',
        'name_en',
        'name_ru',
        'color',
        'message_uz',
        'message_en',
        'message_ru',
        'type',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_uz')
            ->saveSlugsTo('slug');
    }

    protected $appends = ['name', 'message'];

    public function getNameAttribute()
    {
        return [
            'uz' => $this->name_uz,
            'ru' => $this->name_ru,
            'en' => $this->name_en,
        ];
    }

    public function getMessageAttribute()
    {
        return [
            'uz' => $this->message_uz,
            'ru' => $this->message_ru,
            'en' => $this->message_en,
        ];
    }
}
