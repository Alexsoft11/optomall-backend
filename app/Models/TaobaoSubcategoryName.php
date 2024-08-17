<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TaobaoSubcategoryName extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcategory_name_uz',
        'subcategory_name_en',
        'subcategory_name_ru',
        'unique',
        'unique_id',
        'taobao_category_id',
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

    public function category()
    {
        return $this->belongsTo(TaobaoCategory::class, 'taobao_category_id', 'unique_id');
    }

    public function items()
    {
        return $this->hasMany(TaobaoSubcategory::class, 'taobao_subcategory_name_id', 'unique_id');
    }
}
