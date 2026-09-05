<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\ChaplaincyService;

class ChaplaincyController extends AbstractImageVideoController
{
    protected string $resourceKey = 'chaplaincy';
    protected ?string $collectionKey = 'chaplaincies';

    public function __construct()
    {
        parent::__construct();
        $this->service = new ChaplaincyService();
    }
}
