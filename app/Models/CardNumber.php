<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardNumber extends Model
{
    use HasFactory;

    protected $fillable = ['card_number', 'money', 'type'];

    public function checks()
    {
        return $this->hasMany(OrderCheck::class, 'card_number_id', 'id');
    }
}
