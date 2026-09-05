<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\PageView;

/** Lightweight hit-counter, no package - one row per page view, fired client-side from public pages. */
class PageViewService extends Service
{
    public function __construct(private PageView $model = new PageView())
    {
    }

    public function record(string $pageUrl, ?string $pageType, ?int $pageId, string $ip, string $userAgent): void
    {
        $this->model->create([
            'page_url' => mb_substr($pageUrl, 0, 255),
            'page_type' => $pageType,
            'page_id' => $pageId,
            'ip_address' => $ip,
            'user_agent' => mb_substr($userAgent, 0, 255),
            'view_date' => date('Y-m-d'),
        ]);
    }

    public function statsFor(string $pageType, int $pageId): array
    {
        $total = $this->model->count(['page_type' => $pageType, 'page_id' => $pageId]);
        $today = $this->model->count(['page_type' => $pageType, 'page_id' => $pageId, 'view_date' => date('Y-m-d')]);

        return ['total_views' => $total, 'daily_views' => [['date' => date('Y-m-d'), 'views' => $today]]];
    }
}
