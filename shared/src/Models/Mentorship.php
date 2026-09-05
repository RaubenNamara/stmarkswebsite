<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Mentorship extends Model
{
    protected string $table = 'mentorships';
    protected array $fillable = ['title', 'caption', 'description', 'image', 'video', 'video_link'];
    protected array $searchable = ['title'];
}
