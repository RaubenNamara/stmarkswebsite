<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\MediaService;

class MediaController extends Controller
{
    private MediaService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new MediaService();
    }

    public function index(): void
    {
        $this->success(['media' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['file'] ?? null);
        $result['ok'] ? $this->success(['media' => $this->service->all()], 'Media item created') : $this->validationError($result['errors']);
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Media item deleted') : $this->notFound();
    }
}
