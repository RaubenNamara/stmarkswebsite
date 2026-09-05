<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'title',
        'type',
        'file_path',
        'video_url',
        'is_active'
    ];
}