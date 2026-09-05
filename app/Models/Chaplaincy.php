<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chaplaincy extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image',
        'video',
        'video_link',
    ];
}