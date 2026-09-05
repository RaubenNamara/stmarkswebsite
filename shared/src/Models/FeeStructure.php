<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class FeeStructure extends Model
{
    protected string $table = 'fee_structures';
    protected array $fillable = ['title', 'file_path'];
    protected array $searchable = ['title'];
}
