<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardNumber extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('is_active', true)
                    ->where('type', $model->type)
                    ->update(['is_active' => false]);
            }
        });
    }

    protected $fillable = ['card_number','name', 'money', 'type', 'is_active'];

    public function checks()
    {
        return $this->hasMany(OrderCheck::class, 'card_number_id', 'id');
    }
}
