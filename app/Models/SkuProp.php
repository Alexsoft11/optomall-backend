<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuProp extends Model
{
    use HasFactory;

    protected $fillable = ['pid', 'prop_name', 'product_local_id'];

    public function values()
    {
        return $this->hasMany(SkuValue::class);
    }
}
