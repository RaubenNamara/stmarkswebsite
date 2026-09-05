<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\FeeStructure;

class FeeStructureService extends PdfContentService
{
    public function __construct()
    {
        parent::__construct(new FeeStructure(), 'fee-structures', 'file_path');
    }
}
