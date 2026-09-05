<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\SmosaAlumniService;

class SmosaAlumniController extends AbstractPublicListController
{
    protected string $collectionKey = 'smosa';

    public function __construct()
    {
        parent::__construct();
        $this->service = new SmosaAlumniService();
    }
}
