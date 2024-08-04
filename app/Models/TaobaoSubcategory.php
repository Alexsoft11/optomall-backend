<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoSubcategory extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'subcategory_uz',
        'subcategory_en',
        'subcategory_ru',
        'slug',
    ];

    public function subcategoryName()
    {
        return $this->belongsTo(TaobaoSubcategoryName::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('subcategory_en')
            ->saveSlugsTo('slug');
    }
}
