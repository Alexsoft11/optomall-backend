<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuValue extends Model
{
    use HasFactory;

    protected $fillable = ['sku_prop_id', 'vid', 'name', 'imageUrl'];

    public function skuProp()
    {
        return $this->belongsTo(SkuProp::class);
    }
}
