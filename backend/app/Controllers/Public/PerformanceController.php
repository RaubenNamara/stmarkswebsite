<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Services\PerformanceService;

class PerformanceController extends Controller
{
    private PerformanceService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PerformanceService();
    }

    public function index(): void
    {
        $this->success(['items' => $this->service->all()]);
    }

    /** Only serves files stored via the new UploadService (/uploads/...) - legacy rows are opened via their absolute Assets::resolve() URL directly by the frontend instead. */
    public function pdf(string $id): void
    {
        $item = $this->service->find((int) $id);
        if (!$item || !$item['pdf'] || !str_starts_with($item['pdf'], '/uploads/')) {
            $this->notFound();
            return;
        }

        $absolute = Config::getUploadsPath(substr($item['pdf'], strlen('/uploads/')));
        if (!is_file($absolute)) {
            $this->notFound();
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9 _.-]/', '', $item['title']) . '.pdf"');
        header('Content-Length: ' . filesize($absolute));
        readfile($absolute);
        exit;
    }
}
