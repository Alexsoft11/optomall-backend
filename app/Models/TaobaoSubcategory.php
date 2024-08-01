<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaobaoSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcategory_uz',
        'subcategory_en',
        'subcategory_ru'
    ];

    public function subcategoryName()
    {
        return $this->belongsTo(TaobaoSubcategoryName::class);
    }
}
