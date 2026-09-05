<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = [
        'image_path',
        'video_path',
        'type',
        'title',
        'caption',
        'order',
        'is_active'
    ];

    protected $appends = ['media_url'];

    public function getMediaUrlAttribute()
    {
        if ($this->type === 'video' && $this->video_path) {
            return asset('storage/' . $this->video_path);
        }

        return asset('storage/' . $this->image_path);
    }
}