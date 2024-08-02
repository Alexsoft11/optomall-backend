<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaobaoCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_uz',
        'name_en',
        'name_ru',
        'is_active',
    ];

    public function subcategories()
    {
        return $this->hasMany(TaobaoSubcategoryName::class);
    }
}
