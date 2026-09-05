<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\CampusVoiceService;

class CampusVoiceController
{
    private CampusVoiceService $service;

    public function __construct()
    {
        $this->service = new CampusVoiceService();
    }

    public function index(): void
    {
        View::render('campus-voices/index', ['articles' => $this->service->published()], meta: ['title' => 'Campus Voices']);
    }

    public function show(string $slug): void
    {
        $article = $this->service->findPublishedBySlug($slug);
        if (!$article) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        View::render('campus-voices/show', ['article' => $article], meta: [
            'title' => $article['title'],
            'description' => $article['summary'],
        ]);
    }
}
