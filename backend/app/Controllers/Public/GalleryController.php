<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\GalleryService;

class GalleryController extends Controller
{
    public function index(): void
    {
        $this->success(['events' => (new GalleryService())->all()]);
    }
}
