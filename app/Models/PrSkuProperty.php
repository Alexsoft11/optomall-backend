<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrSkuProperty extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'name', 'values'];

    protected $casts = [
        'values' => 'array', // Store multiple values for a property in an array format
    ];

    public function product()
    {
        return $this->belongsTo(ProductLocal::class, 'product_id');
    }
}
