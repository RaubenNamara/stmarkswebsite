<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryEvent extends Model
{
    protected $fillable = [
        'title',
        'description'
    ];

    public function images()
    {
        return $this->hasMany(GalleryImage::class, 'gallery_event_id');
    }
}