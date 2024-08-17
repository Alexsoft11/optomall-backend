<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoCategory extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name_uz',
        'name_en',
        'name_ru',
        'unique',
        'unique_id',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = random_int(10000, 99999);

            $model->unique_id = $id;
        });
    }

    // protected static function idExistsInAnyTable($id)
    // {
    //     // Check if the ID exists in any of the related tables
    //     return DB::table('taobao_categories')->where('id', $id)->exists() ||
    //         DB::table('taobao_subcategory_names')->where('id', $id)->exists() ||
    //         DB::table('taobao_subcategories')->where('id', $id)->exists();
    // }

    public function subcategories()
    {
        return $this->hasMany(TaobaoSubcategoryName::class, 'taobao_category_id', 'unique_id');
    }
}
