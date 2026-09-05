<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\CampusVoiceService;

class CampusVoiceController extends Controller
{
    private CampusVoiceService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CampusVoiceService();
    }

    public function index(): void
    {
        $this->success(['articles' => $this->service->published()]);
    }

    public function show(string $slug): void
    {
        $article = $this->service->findPublishedBySlug($slug);
        if (!$article) {
            $this->notFound('Article not found');
            return;
        }

        $this->success(['article' => $article]);
    }
}
