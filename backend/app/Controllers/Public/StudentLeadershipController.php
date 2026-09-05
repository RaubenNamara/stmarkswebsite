<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\StudentLeadershipService;

class StudentLeadershipController extends AbstractPublicListController
{
    protected string $collectionKey = 'student_leadership';

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudentLeadershipService();
    }
}
