<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ImageVideoContentService;

/** Shared index/store/update/destroy for the ~7 ImageVideoContentService-backed domains. */
abstract class AbstractImageVideoController extends Controller
{
    protected ImageVideoContentService $service;
    protected string $resourceKey;
    protected ?string $collectionKey = null;

    public function index(): void
    {
        $this->success([$this->collectionKey ?? $this->resourceKey . 's' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['image'] ?? null, $_FILES['video'] ?? null);
        $result['ok']
            ? $this->success([$this->resourceKey => $this->service->find($result['id'])], 'Created')
            : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $_FILES['image'] ?? null, $_FILES['video'] ?? null);
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success([$this->resourceKey => $this->service->find($result['id'])], 'Updated');
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Deleted') : $this->notFound();
    }
}
