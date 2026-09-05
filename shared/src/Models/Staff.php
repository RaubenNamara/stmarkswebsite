<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Staff extends Model
{
    protected string $table = 'staff';
    protected array $fillable = ['name', 'department', 'category', 'sort_order', 'photo'];
    protected array $searchable = ['name', 'department'];

    public function allOrdered(): array
    {
        return $this->all([], ['sort_order' => 'ASC', 'name' => 'ASC']);
    }
}
