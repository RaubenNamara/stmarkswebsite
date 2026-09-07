<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\SlideService;

class SlideController extends Controller
{
    private SlideService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SlideService();
    }

    public function index(): void
    {
        $this->success(['slides' => $this->service->allWithUrls()]);
    }
}
