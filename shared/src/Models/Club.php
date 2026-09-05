<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Club extends Model
{
    protected string $table = 'clubs';
    protected array $fillable = ['title', 'slug', 'content'];
    protected array $searchable = ['title'];

    public function findBySlug(string $slug): ?array
    {
        return $this->first(['slug' => $slug]);
    }
}
