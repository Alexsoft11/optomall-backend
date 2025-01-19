<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLocal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'status',
        'category_id',
        'brand',
    ];

    public function skus()
    {
        return $this->hasMany(PrSku::class, 'product_local_id');
    }

    public function images()
    {
        return $this->hasMany(PrImage::class, 'product_local_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
