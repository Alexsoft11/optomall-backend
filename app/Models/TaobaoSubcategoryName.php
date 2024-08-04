<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoSubcategoryName extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'subcategory_name_uz',
        'subcategory_name_en',
        'subcategory_name_ru',
        'unique',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(TaobaoCategory::class);
    }

    public function items()
    {
        return $this->hasMany(TaobaoSubcategory::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('subcategory_name_en')
            ->saveSlugsTo('slug');
    }
}
