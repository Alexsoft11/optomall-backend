<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaobaoSubcategoryName extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcategory_name_uz',
        'subcategory_name_en',
        'subcategory_name_ru',
    ];

    public function category()
    {
        return $this->belongsTo(TaobaoCategory::class);
    }

    public function items()
    {
        return $this->hasMany(TaobaoSubcategory::class);
    }
}
