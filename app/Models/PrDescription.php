<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrDescription extends Model
{
    protected $table = 'pr_descriptions';
    protected $fillable = ['product_local_id', 'image'];

    protected $casts = [
        'image' => 'array',
    ];

    public $timestamps = false;

    public function productLocal()
    {
        return $this->belongsTo(ProductLocal::class, 'product_local_id');
    }
}
