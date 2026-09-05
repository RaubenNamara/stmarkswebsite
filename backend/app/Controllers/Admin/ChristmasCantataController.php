<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\ChristmasCantataService;

class ChristmasCantataController extends AbstractImageVideoController
{
    protected string $resourceKey = 'cantata';
    protected ?string $collectionKey = 'cantatas';

    public function __construct()
    {
        parent::__construct();
        $this->service = new ChristmasCantataService();
    }
}
