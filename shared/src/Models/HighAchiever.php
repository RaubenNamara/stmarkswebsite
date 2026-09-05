<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class HighAchiever extends Model
{
    protected string $table = 'high_achievers';
    protected array $fillable = ['name', 'photo', 'year', 'exam', 'division', 'description'];
    protected array $searchable = ['name', 'exam'];
}
