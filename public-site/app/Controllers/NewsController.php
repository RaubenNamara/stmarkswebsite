<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\NewsService;

class NewsController
{
    private NewsService $newsService;

    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    public function index(): void
    {
        $news = $this->newsService->published(20);

        View::render('news/index', ['newsItems' => $news], meta: [
            'title' => 'News - ' . "St Mark's College Namagoma",
        ]);
    }

    public function show(string $slug): void
    {
        $news = $this->newsService->findBySlug($slug);

        if (!$news || !$news['is_published']) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        View::render('news/show', ['news' => $news], meta: [
            'title' => $news['title'],
            'description' => $news['excerpt'],
        ]);
    }
}
