<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'slug', 'content', 'is_published'];
    protected array $searchable = ['title'];
}
