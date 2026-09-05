<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Shared\Services\GirlBoyTalkService;

class GirlBoyTalkController extends AbstractImageVideoController
{
    protected string $resourceKey = 'talk';

    public function __construct()
    {
        parent::__construct();
        $this->service = new GirlBoyTalkService();
    }
}
