<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skus extends Model
{
    use HasFactory;
    protected $table = 'skus';
    protected $fillable = [
        'sku_id',
        'product_local_id',
        'quantity',
        'price',
        'total_price',
        'properties',
        'properties_name',
        'sale_count',
        "specid",
    ];

    public function product()
    {
        return $this->belongsTo(ProductLocal::class, 'product_local_id');
    }
}
