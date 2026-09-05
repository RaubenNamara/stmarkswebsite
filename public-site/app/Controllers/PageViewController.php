<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\Shared\Services\PageViewService;

/** Fired client-side (or server-side via a 1px include) from public pages to record a view. */
class PageViewController
{
    public function store(): void
    {
        $service = new PageViewService();
        $pageId = isset($_GET['page_id']) ? (int) $_GET['page_id'] : null;

        $service->record(
            (string) ($_GET['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '/'),
            isset($_GET['page_type']) ? (string) $_GET['page_type'] : null,
            $pageId,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
}
