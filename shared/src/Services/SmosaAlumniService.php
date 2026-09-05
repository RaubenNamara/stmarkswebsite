<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\SmosaAlumni;

class SmosaAlumniService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(
            new SmosaAlumni(),
            'smosa',
            ['name' => ['required', 'max:255']],
            'photo',
            'video'
        );
    }
}
