<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\ChristmasCantataService;

class ChristmasCantataController extends AbstractPublicListController
{
    protected string $collectionKey = 'christmas_cantata';

    public function __construct()
    {
        parent::__construct();
        $this->service = new ChristmasCantataService();
    }
}
