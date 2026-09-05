<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Services\PerformanceService;

class PerformanceController
{
    private PerformanceService $service;

    public function __construct()
    {
        $this->service = new PerformanceService();
    }

    public function index(): void
    {
        View::render('performances/index', ['items' => $this->service->all()], meta: ['title' => 'Performance & Circulars']);
    }

    public function pdf(string $id): void
    {
        $item = $this->service->find((int) $id);
        if (!$item || !$item['pdf']) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        if (!str_starts_with($item['pdf'], '/uploads/')) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        $absolute = Config::getUploadsPath(substr($item['pdf'], strlen('/uploads/')));
        if (!is_file($absolute)) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9 _.-]/', '', $item['title']) . '.pdf"');
        header('Content-Length: ' . filesize($absolute));
        readfile($absolute);
        exit;
    }
}
