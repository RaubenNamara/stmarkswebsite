<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ContactService;

class ContactController extends Controller
{
    private ContactService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ContactService();
    }

    public function index(): void
    {
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $this->success($this->service->paginate($page, $limit));
    }

    public function markRead(string $id): void
    {
        $this->service->markRead((int) $id) ? $this->success([], 'Marked as read') : $this->notFound();
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Contact deleted') : $this->notFound();
    }
}
