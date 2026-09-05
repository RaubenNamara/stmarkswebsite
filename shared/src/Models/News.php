<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class News extends Model
{
    protected string $table = 'news';
    protected array $fillable = ['title', 'slug', 'excerpt', 'content', 'image_path', 'is_published', 'published_at'];
    protected array $searchable = ['title', 'excerpt'];

    public function published(int $limit = 0, int $offset = 0): array
    {
        return $this->all(['is_published' => 1], ['published_at' => 'DESC'], $limit, $offset);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->first(['slug' => $slug]);
    }
}
