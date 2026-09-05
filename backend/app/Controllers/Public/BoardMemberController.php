<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\BoardMemberService;

class BoardMemberController extends AbstractPublicListController
{
    protected string $collectionKey = 'board_members';

    public function __construct()
    {
        parent::__construct();
        $this->service = new BoardMemberService();
    }
}
