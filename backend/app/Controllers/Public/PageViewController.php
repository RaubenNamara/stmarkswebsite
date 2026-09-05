<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\PageViewService;

/** Fired client-side from public pages to record a view. */
class PageViewController extends Controller
{
    public function store(): void
    {
        $service = new PageViewService();

        $service->record(
            (string) $this->input('page_url', '/'),
            $this->input('page_type') !== null ? (string) $this->input('page_type') : null,
            $this->input('page_id') !== null ? (int) $this->input('page_id') : null,
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        $this->success();
    }
}
