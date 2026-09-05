<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\GalleryService;

class GalleryController extends Controller
{
    private GalleryService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GalleryService();
    }

    public function index(): void
    {
        $this->success(['events' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->createEvent($this->input(), $this->normalizeFilesArray('images'));
        $result['ok'] ? $this->success(['events' => $this->service->all()], 'Gallery event created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->updateEvent((int) $id, $this->input());
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['events' => $this->service->all()], 'Gallery event updated');
    }

    public function addImages(string $id): void
    {
        $result = $this->service->addImages((int) $id, $this->normalizeFilesArray('images'));
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['events' => $this->service->all()], 'Images added');
    }

    public function destroyImage(string $imageId): void
    {
        $this->service->deleteImage((int) $imageId) ? $this->success(['events' => $this->service->all()], 'Image deleted') : $this->notFound();
    }

    public function destroy(string $id): void
    {
        $this->service->deleteEvent((int) $id) ? $this->success([], 'Gallery event deleted') : $this->notFound();
    }
}
