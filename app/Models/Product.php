<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id', 'title', 'price', 'image', 'location', 'product_url',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function pictures()
    {
        return $this->hasMany(ProductPicture::class);
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function compositions()
    {
        return $this->hasMany(ProductComposition::class);
    }
}

class ProductPicture extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'image_url', 'is_main',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class ProductVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'preview_url', 'url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class ProductColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'property_name', 'value', 'image_url', 'mini_image_url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class ProductSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'property_name', 'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class ProductComposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'property_name', 'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
