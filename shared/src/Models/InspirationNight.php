<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class InspirationNight extends Model
{
    protected string $table = 'inspiration_nights';
    protected array $fillable = ['title', 'description', 'image', 'video', 'date', 'speaker'];
    protected array $searchable = ['title', 'speaker'];
}
