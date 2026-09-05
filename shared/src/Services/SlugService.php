<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Model;

/**
 * Generates a unique URL slug from a title, appending -2, -3, ... on collision. Consolidates
 * what the old Laravel app duplicated across News/Clubs/CampusVoices controllers.
 */
class SlugService extends Service
{
    public function unique(Model $model, string $title, ?int $excludeId = null): string
    {
        $base = $this->slugify($title);
        $slug = $base;
        $i = 2;

        while ($this->exists($model, $slug, $excludeId)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function slugify(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'item';
    }

    private function exists(Model $model, string $slug, ?int $excludeId): bool
    {
        $existing = $model->first(['slug' => $slug]);
        if (!$existing) {
            return false;
        }

        return $excludeId === null || (int) $existing['id'] !== $excludeId;
    }
}
