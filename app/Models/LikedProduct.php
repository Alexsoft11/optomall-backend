<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'title_uz',
        'title_en',
        'title_ru',
        'price',
        'image'
    ];
}
