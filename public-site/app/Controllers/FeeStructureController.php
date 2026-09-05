<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Services\FeeStructureService;

class FeeStructureController
{
    private FeeStructureService $service;

    public function __construct()
    {
        $this->service = new FeeStructureService();
    }

    public function index(): void
    {
        View::render('fee-structures/index', ['items' => $this->service->all()], meta: [
            'title' => 'Fee Structures, Personal Needs, School Rules & Calendar',
        ]);
    }

    public function pdf(string $id): void
    {
        $item = $this->service->find((int) $id);
        if (!$item || !$item['file_path']) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        $this->streamLocalPdf($item['file_path'], $item['title']);
    }

    /** Only serves files stored via the new UploadService (/uploads/...) - legacy rows are opened via their absolute Assets::resolve() URL instead, not streamed through here. */
    private function streamLocalPdf(string $relativePath, string $title): void
    {
        if (!str_starts_with($relativePath, '/uploads/')) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        $absolute = Config::getUploadsPath(substr($relativePath, strlen('/uploads/')));
        if (!is_file($absolute)) {
            http_response_code(404);
            View::render('errors/404', [], meta: ['title' => 'Page not found']);
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9 _.-]/', '', $title) . '.pdf"');
        header('Content-Length: ' . filesize($absolute));
        readfile($absolute);
        exit;
    }
}
