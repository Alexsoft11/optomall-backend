<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends BaseModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_group_id', 'amount', 'type'];
}
