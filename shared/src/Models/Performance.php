<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Performance extends Model
{
    protected string $table = 'performances';
    protected array $fillable = ['title', 'pdf'];
    protected array $searchable = ['title'];
}
