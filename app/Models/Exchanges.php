<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exchanges extends BaseModel
{
    use HasFactory;

    protected $fillable = ['currency', 'amount'];
}
