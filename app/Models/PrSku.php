<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrSku extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_local_id',
        'sku',
        'stock',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(ProductLocal::class, 'product_local_id');
    }
}
