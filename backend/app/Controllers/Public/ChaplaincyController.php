<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\ChaplaincyService;

class ChaplaincyController extends AbstractPublicListController
{
    protected string $collectionKey = 'chaplaincy';

    public function __construct()
    {
        parent::__construct();
        $this->service = new ChaplaincyService();
    }
}
