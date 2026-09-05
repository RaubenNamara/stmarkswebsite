<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class StudentLeadership extends Model
{
    protected string $table = 'student_leaderships';
    protected array $fillable = ['title', 'content', 'image_path', 'video_path', 'video_link'];
    protected array $searchable = ['title'];
}
