<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\GirlBoyTalkService;

class GirlBoyTalkController extends AbstractPublicListController
{
    protected string $collectionKey = 'girlboytalk';

    public function __construct()
    {
        parent::__construct();
        $this->service = new GirlBoyTalkService();
    }
}
