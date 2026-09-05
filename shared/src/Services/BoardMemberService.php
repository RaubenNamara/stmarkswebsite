<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\BoardMember;

class BoardMemberService extends SinglePhotoContentService
{
    public function __construct()
    {
        parent::__construct(
            new BoardMember(),
            'board-members',
            ['name' => ['required', 'max:255'], 'position' => ['required', 'max:255']]
        );
    }
}
