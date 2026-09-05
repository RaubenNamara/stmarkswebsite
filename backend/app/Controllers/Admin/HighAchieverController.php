<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\HighAchieverService;

class HighAchieverController extends Controller
{
    private HighAchieverService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new HighAchieverService();
    }

    public function index(): void
    {
        $this->success(['high_achievers' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['photo'] ?? null);
        $result['ok'] ? $this->success(['high_achiever' => $this->service->find($result['id'])], 'High achiever created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $_FILES['photo'] ?? null);
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['high_achiever' => $this->service->find($result['id'])], 'High achiever updated');
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'High achiever deleted') : $this->notFound();
    }
}
