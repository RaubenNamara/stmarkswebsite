<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Slide extends Model
{
    protected string $table = 'slides';
    protected array $fillable = ['title', 'caption', 'type', 'image_path', 'video_path', 'order', 'is_active'];

    public function allOrdered(): array
    {
        return $this->all([], ['order' => 'ASC']);
    }
}
