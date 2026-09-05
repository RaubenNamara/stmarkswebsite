<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\PostService;

class PostController
{
    private PostService $service;

    public function __construct()
    {
        $this->service = new PostService();
    }

    public function index(): void
    {
        View::render('posts/index', ['posts' => $this->service->published()], meta: ['title' => 'Posts']);
    }

    public function show(string $slug): void
    {
        $post = $this->service->findPublishedBySlug($slug);
        if (!$post) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        View::render('posts/show', ['post' => $post], meta: ['title' => $post['title']]);
    }
}
