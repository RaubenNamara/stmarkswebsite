<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class GirlBoyTalk extends Model
{
    protected string $table = 'girl_boy_talks';
    protected array $fillable = ['title', 'description', 'image', 'video', 'video_link'];
    protected array $searchable = ['title'];
}
