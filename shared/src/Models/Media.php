<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Media extends Model
{
    protected string $table = 'media';
    protected array $fillable = ['title', 'type', 'file_path', 'video_url', 'is_active'];
    protected array $searchable = ['title'];
}
