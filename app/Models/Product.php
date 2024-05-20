<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'name',
        'image',
        'sell_count',
        'description',
        'price',
        'discount',
        'new_price',
        'rating',
        'features',
        'comments',
    ];

    protected $casts = [
        'rating' => 'array',
        'features' => 'array',
        'comments' => 'array',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
