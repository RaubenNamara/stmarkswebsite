<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class ClubImage extends Model
{
    protected string $table = 'club_images';
    protected array $fillable = ['club_id', 'image_path', 'caption'];

    public function forClub(int $clubId): array
    {
        return $this->all(['club_id' => $clubId], ['created_at' => 'ASC']);
    }
}
