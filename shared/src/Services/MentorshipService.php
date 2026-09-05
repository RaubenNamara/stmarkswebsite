<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Mentorship;

class MentorshipService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(new Mentorship(), 'mentorship');
    }
}
