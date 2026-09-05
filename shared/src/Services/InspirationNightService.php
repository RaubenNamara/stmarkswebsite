<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\InspirationNight;

class InspirationNightService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(
            new InspirationNight(),
            'inspiration',
            ['title' => ['required', 'max:255'], 'description' => ['required']]
        );
    }
}
