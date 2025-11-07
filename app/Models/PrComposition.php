<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrComposition extends Model
{
    protected $table = 'pr_compositions';
    protected $fillable = ['product_local_id', 'composition', 'composition_name'];
    public $timestamps = false;

    public function productLocal()
    {
        return $this->belongsTo(ProductLocal::class, 'product_local_id');
    }
}
