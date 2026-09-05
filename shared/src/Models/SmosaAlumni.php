<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class SmosaAlumni extends Model
{
    protected string $table = 'smosa_alumnis';
    protected array $fillable = ['name', 'photo', 'profession', 'message', 'video'];
    protected array $searchable = ['name', 'profession'];
}
