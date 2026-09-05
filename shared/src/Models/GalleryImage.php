<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class GalleryImage extends Model
{
    protected string $table = 'gallery_images';
    protected array $fillable = ['gallery_event_id', 'image_path'];

    public function forEvent(int $eventId): array
    {
        return $this->all(['gallery_event_id' => $eventId], ['created_at' => 'ASC']);
    }
}
