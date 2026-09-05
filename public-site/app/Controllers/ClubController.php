<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\ClubService;

class ClubController
{
    private ClubService $service;

    public function __construct()
    {
        $this->service = new ClubService();
    }

    public function index(): void
    {
        View::render('clubs/index', ['clubs' => $this->service->all()], meta: ['title' => 'Clubs']);
    }

    public function show(string $slug): void
    {
        $club = $this->service->findBySlug($slug);
        if (!$club) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        View::render('clubs/show', ['club' => $club], meta: ['title' => $club['title']]);
    }
}
