<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\MediaService;

class MediaController extends Controller
{
    private MediaService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new MediaService();
    }

    public function index(): void
    {
        $this->success(['media' => $this->service->all()]);
    }
}
