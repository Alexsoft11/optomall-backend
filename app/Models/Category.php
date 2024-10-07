<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = ['name_uz', 'name_ru', 'name_en', 'image_mobile', 'image', 'is_active', 'catalog_id', 'url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function bannerProducts()
    {
        return $this->hasMany(BannerProduct::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }
}
