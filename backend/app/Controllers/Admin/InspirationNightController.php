<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\InspirationNightService;

class InspirationNightController extends AbstractImageVideoController
{
    protected string $resourceKey = 'inspiration_night';

    public function __construct()
    {
        parent::__construct();
        $this->service = new InspirationNightService();
    }
}
