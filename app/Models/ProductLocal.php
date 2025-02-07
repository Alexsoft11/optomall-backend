<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLocal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'category_id',
        'brand',
        'quantity',
        'sale_count'
    ];

    public function skus()
    {
        return $this->hasMany(Skus::class, 'product_local_id');
    }

    public function sku_props()
    {
        return $this->hasMany(SkuProp::class, 'product_local_id');
    }

    public function compositions()
    {
        return $this->hasMany(PrComposition::class, 'product_local_id');
    }

    public function images()
    {
        return $this->hasMany(PrImage::class, 'product_local_id');
    }

    public function mainImage()
    {
        return $this->hasOne(PrImage::class)->where('is_main', true);
    }


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function descriptions()
    {
        return $this->hasMany(PrDescription::class, 'product_local_id');
    }
}
