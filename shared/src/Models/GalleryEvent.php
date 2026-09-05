<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class GalleryEvent extends Model
{
    protected string $table = 'gallery_events';
    protected array $fillable = ['title'];
    protected array $searchable = ['title'];
}
