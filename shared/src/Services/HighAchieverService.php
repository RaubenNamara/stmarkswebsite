<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\HighAchiever;

class HighAchieverService extends SinglePhotoContentService
{
    public function __construct()
    {
        parent::__construct(
            new HighAchiever(),
            'high-achievers',
            ['name' => ['required', 'max:255'], 'year' => ['required'], 'exam' => ['required', 'max:255']]
        );
    }
}
