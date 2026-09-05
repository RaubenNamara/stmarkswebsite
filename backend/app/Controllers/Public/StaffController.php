<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\StaffService;

class StaffController extends Controller
{
    public function index(): void
    {
        $this->success(['groups' => (new StaffService())->groupedForDisplay()]);
    }
}
