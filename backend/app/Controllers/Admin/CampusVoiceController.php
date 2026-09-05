<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

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
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $this->success($this->service->paginate($page, $limit, (string) $this->query('search', '')));
    }

    public function show(string $id): void
    {
        $row = $this->service->find((int) $id);
        $row ? $this->success(['campus_voice' => $row]) : $this->notFound();
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['featured_image'] ?? null);
        $result['ok'] ? $this->success(['campus_voice' => $this->service->find($result['id'])], 'Article created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $_FILES['featured_image'] ?? null);
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['campus_voice' => $this->service->find($result['id'])], 'Article updated');
    }

    public function toggleFeatured(string $id): void
    {
        $result = $this->service->toggleFeatured((int) $id);
        $result['ok'] ? $this->success(['campus_voice' => $this->service->find((int) $id)], 'Updated') : $this->notFound();
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Article deleted') : $this->notFound();
    }
}
