<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ClubService;

class ClubController extends Controller
{
    private ClubService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClubService();
    }

    public function index(): void
    {
        $this->success(['clubs' => $this->service->all()]);
    }

    public function show(string $id): void
    {
        $row = $this->service->find((int) $id);
        $row ? $this->success(['club' => $row]) : $this->notFound();
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $this->normalizeFilesArray('images'));
        $result['ok'] ? $this->success(['club' => $this->service->find($result['id'])], 'Club created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $this->normalizeFilesArray('images'));
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['club' => $this->service->find($result['id'])], 'Club updated');
    }

    public function destroyImage(string $imageId): void
    {
        $this->service->deleteImage((int) $imageId) ? $this->success([], 'Image deleted') : $this->notFound();
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Club deleted') : $this->notFound();
    }
}
