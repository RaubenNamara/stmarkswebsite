<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Chaplaincy extends Model
{
    protected string $table = 'chaplaincies';
    protected array $fillable = ['title', 'content', 'image', 'video', 'video_link'];
    protected array $searchable = ['title'];
}
