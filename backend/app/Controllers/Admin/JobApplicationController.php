<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\JobApplicationService;

class JobApplicationController extends Controller
{
    private JobApplicationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new JobApplicationService();
    }

    public function index(): void
    {
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $this->success($this->service->paginate($page, $limit, (string) $this->query('search', '')));
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Application deleted') : $this->notFound();
    }
}
