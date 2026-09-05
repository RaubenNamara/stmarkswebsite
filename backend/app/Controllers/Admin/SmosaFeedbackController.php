<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\SmosaFeedbackService;

class SmosaFeedbackController extends Controller
{
    private SmosaFeedbackService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SmosaFeedbackService();
    }

    public function index(): void
    {
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $result = $this->service->paginate($page, $limit);
        $result['stats'] = $this->service->stats();
        $this->success($result);
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Feedback deleted') : $this->notFound();
    }
}
