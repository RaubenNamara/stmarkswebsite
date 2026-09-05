<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class ChristmasCantata extends Model
{
    protected string $table = 'christmas_cantatas';
    protected array $fillable = ['title', 'choir', 'date', 'description', 'image', 'video'];
    protected array $searchable = ['title', 'choir'];
}
