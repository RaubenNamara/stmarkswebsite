<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\ChristmasCantata;

class ChristmasCantataService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(new ChristmasCantata(), 'christmas-cantata');
    }
}
