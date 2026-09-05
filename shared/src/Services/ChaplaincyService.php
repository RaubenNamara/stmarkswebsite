<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Chaplaincy;

class ChaplaincyService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(new Chaplaincy(), 'chaplaincy');
    }
}
