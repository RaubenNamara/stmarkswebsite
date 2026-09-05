<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\CoCurricularService;

class CoCurricularController extends AbstractImageVideoController
{
    protected string $resourceKey = 'co_curricular';

    public function __construct()
    {
        parent::__construct();
        $this->service = new CoCurricularService();
    }
}
