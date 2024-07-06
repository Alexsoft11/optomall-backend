<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Product Model
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id', 'title', 'price', 'image', 'location', 'product_url', 'description', 'quantity', 'discount_percent', 'discounted_price', 'rating', 'sold'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function pictures()
    {
        return $this->hasMany(ProductPicture::class, 'product_id');
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class, 'product_id');
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }

    public function compositions()
    {
        return $this->hasMany(ProductComposition::class, 'product_id');
    }
}

// ProductPicture Model
class ProductPicture extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'image_url', 'is_main',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

// ProductVideo Model
class ProductVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'preview_url', 'url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

// ProductColor Model
class ProductColor extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'product_id', 'property_name', 'value', 'image_url', 'mini_image_url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

// ProductSize Model
class ProductSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'property_name', 'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

// ProductComposition Model
class ProductComposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'property_name', 'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
