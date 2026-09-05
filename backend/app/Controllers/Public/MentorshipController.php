<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\MentorshipService;

class MentorshipController extends AbstractPublicListController
{
    protected string $collectionKey = 'mentorship';

    public function __construct()
    {
        parent::__construct();
        $this->service = new MentorshipService();
    }
}
