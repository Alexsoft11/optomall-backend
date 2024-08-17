<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name_uz', 'name_ru', 'name_en'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

}
