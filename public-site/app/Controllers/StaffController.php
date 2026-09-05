<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\StaffService;

class StaffController
{
    public function index(): void
    {
        $groups = (new StaffService())->groupedForDisplay();
        View::render('staff/index', ['groups' => $groups], meta: ['title' => 'Our Staff']);
    }
}
