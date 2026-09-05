<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmosaAlumni extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'profession',
        'message',
        'video'
    ];
}