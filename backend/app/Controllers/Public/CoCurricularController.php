<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\CoCurricularService;

class CoCurricularController extends AbstractPublicListController
{
    protected string $collectionKey = 'co_curricular';

    public function __construct()
    {
        parent::__construct();
        $this->service = new CoCurricularService();
    }
}
