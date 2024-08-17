<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoSubcategory extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'subcategory_uz',
        'subcategory_en',
        'subcategory_ru',
        'unique',
        'unique_id',
        'is_show',
        'taobao_subcategory_name_id'
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

    public function subcategoryName()
    {
        return $this->belongsTo(TaobaoSubcategoryName::class, 'taobao_subcategory_name_id', 'unique_id');
    }

    public function hints()
    {
        return $this->hasMany(Hint::class, 'taobao_subcategory_id', 'unique_id');
    }
}
