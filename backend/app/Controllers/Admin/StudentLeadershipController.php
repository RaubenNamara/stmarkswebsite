<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\StudentLeadershipService;

class StudentLeadershipController extends AbstractImageVideoController
{
    protected string $resourceKey = 'leader';
    protected ?string $collectionKey = 'student_leadership';

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentLeadershipService();
    }
}
