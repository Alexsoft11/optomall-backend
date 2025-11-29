<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsToken extends Model
{
    protected $fillable = ['token', 'expires_at'];

    protected $dates = ['expires_at'];
}
