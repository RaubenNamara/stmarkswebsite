<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\SmosaAlumniService;

class SmosaAlumniController extends AbstractImageVideoController
{
    protected string $resourceKey = 'alumnus';
    protected ?string $collectionKey = 'alumni';

    public function __construct()
    {
        parent::__construct();
        $this->service = new SmosaAlumniService();
    }
}
