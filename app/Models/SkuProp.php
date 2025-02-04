<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuProp extends Model
{
    use HasFactory;

    protected $fillable = ['pid', 'prop_name', 'product_local_id'];
    protected $primaryKey = 'pid';
    public function values()
    {
        return $this->hasMany(SkuValue::class);
    }

    public function sku_values()
    {
        return $this->hasMany(SkuValue::class, 'sku_prop_id'); // Ensure this matches the foreign key in sku_values
    }
}
