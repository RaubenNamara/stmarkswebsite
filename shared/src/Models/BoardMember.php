<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class BoardMember extends Model
{
    protected string $table = 'board_members';
    protected array $fillable = ['name', 'position', 'photo', 'sort_order'];

    public function allOrdered(): array
    {
        return $this->all([], ['sort_order' => 'ASC', 'name' => 'ASC']);
    }
    protected array $searchable = ['name', 'position'];
}
