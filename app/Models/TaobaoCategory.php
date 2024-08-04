<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoCategory extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name_uz',
        'name_en',
        'name_ru',
        'slug',
        'is_active',
    ];

    public function subcategories()
    {
        return $this->hasMany(TaobaoSubcategoryName::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }
}