<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\HighAchieverService;

class HighAchieverController extends AbstractPublicListController
{
    protected string $collectionKey = 'high_achievers';

    public function __construct()
    {
        parent::__construct();
        $this->service = new HighAchieverService();
    }
}
