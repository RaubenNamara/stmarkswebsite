<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\StudentLeadership;

class StudentLeadershipService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(
            new StudentLeadership(),
            'student-leadership',
            ['title' => ['required', 'max:255']],
            'image_path',
            'video_path'
        );
    }
}
