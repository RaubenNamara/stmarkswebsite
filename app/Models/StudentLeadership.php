<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLeadership extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
        'video_path',
        'video_link',
    ];
}