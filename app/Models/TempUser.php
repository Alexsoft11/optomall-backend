<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TempUser extends BaseModel
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email', 'password', 'confirm_password',
    ];
}
