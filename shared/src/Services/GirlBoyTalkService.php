<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\GirlBoyTalk;

class GirlBoyTalkService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(
            new GirlBoyTalk(),
            'girlboytalk',
            ['title' => ['required', 'max:255'], 'description' => ['required']]
        );
    }
}
