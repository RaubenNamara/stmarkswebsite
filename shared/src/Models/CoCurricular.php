<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class CoCurricular extends Model
{
    protected string $table = 'co_curriculars';
    protected array $fillable = ['title', 'content', 'image', 'video'];
    protected array $searchable = ['title'];
}
