<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\PostService;

class PostController extends Controller
{
    private PostService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PostService();
    }

    public function index(): void
    {
        $this->success(['posts' => $this->service->published()]);
    }

    public function show(string $slug): void
    {
        $post = $this->service->findPublishedBySlug($slug);
        if (!$post) {
            $this->notFound('Post not found');
            return;
        }

        $this->success(['post' => $post]);
    }
}
