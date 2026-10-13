<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\XFeedService;

class XFeedController extends Controller
{
    private XFeedService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new XFeedService();
    }

    public function index(): void
    {
        // The service caches X's response server-side; this lets browsers/proxies reuse it briefly too.
        header('Cache-Control: public, max-age=300');
        $this->success($this->service->latest());
    }
}
