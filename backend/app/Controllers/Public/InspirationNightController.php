<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\InspirationNightService;

class InspirationNightController extends AbstractPublicListController
{
    protected string $collectionKey = 'inspiration';

    public function __construct()
    {
        parent::__construct();
        $this->service = new InspirationNightService();
    }
}
