<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    // Allow mass assignment for these columns
    protected $fillable = [
        'gallery_event_id',
        'image_path',
        'title',
        'description',
        'is_active',
    ];

    // Relationship: Image belongs to an event
    public function event()
    {
        return $this->belongsTo(GalleryEvent::class, 'gallery_event_id');
    }
}