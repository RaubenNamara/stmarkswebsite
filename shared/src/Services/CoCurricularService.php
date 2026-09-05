<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\CoCurricular;

class CoCurricularService extends ImageVideoContentService
{
    public function __construct()
    {
        parent::__construct(new CoCurricular(), 'co-curricular');
    }
}
