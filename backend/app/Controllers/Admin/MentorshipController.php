<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\MentorshipService;

class MentorshipController extends AbstractImageVideoController
{
    protected string $resourceKey = 'mentorship';

    public function __construct()
    {
        parent::__construct();
        $this->service = new MentorshipService();
    }
}
