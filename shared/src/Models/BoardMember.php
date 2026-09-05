<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class BoardMember extends Model
{
    protected string $table = 'board_members';
    protected array $fillable = ['name', 'position', 'photo'];
    protected array $searchable = ['name', 'position'];
}
