<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socials extends Model
{
    use HasFactory;

    protected $table = 'socials';

    protected $fillable = ['name', 'icon', 'path', 'file'];

    // public function getFileUrlAttribute()
    // {
    //     return Socials::url($this->file);
    // }
}
