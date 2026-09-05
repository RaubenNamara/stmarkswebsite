<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\NewsService;

class NewsController extends Controller
{
    private NewsService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new NewsService();
    }

    public function index(): void
    {
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $this->success(['news' => $this->service->published($limit)]);
    }

    public function show(string $slug): void
    {
        $news = $this->service->findBySlug($slug);
        if (!$news || !$news['is_published']) {
            $this->notFound('News item not found');
            return;
        }

        $this->success(['news' => $news]);
    }
}
