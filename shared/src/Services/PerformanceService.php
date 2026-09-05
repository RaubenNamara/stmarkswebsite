<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Performance;

class PerformanceService extends PdfContentService
{
    public function __construct()
    {
        parent::__construct(new Performance(), 'performances', 'pdf');
    }
}
