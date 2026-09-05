<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class CampusVoice extends Model
{
    protected string $table = 'campus_voices';
    protected array $fillable = [
        'student_name', 'title', 'slug', 'summary', 'author_bio', 'content',
        'featured_image', 'category', 'author', 'featured', 'status', 'views', 'published_at',
    ];
    protected array $searchable = ['title', 'student_name', 'category'];

    public function findBySlug(string $slug): ?array
    {
        return $this->first(['slug' => $slug]);
    }

    public function incrementViews(int $id): void
    {
        $this->query('UPDATE campus_voices SET views = views + 1 WHERE id = :id', ['id' => $id]);
    }
}
