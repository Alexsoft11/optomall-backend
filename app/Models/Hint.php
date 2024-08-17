<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hint extends Model
{
    use HasFactory;

    protected $fillable = ['taobao_subcategory_id', 'name'];

    public function taobaoSubcategory()
    {
        return $this->belongsTo(TaobaoSubcategory::class, 'taobao_subcategory_id', 'unique_id');
    }
}
