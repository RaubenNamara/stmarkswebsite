<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\FeeStructureService;

class FeeStructureController extends Controller
{
    private FeeStructureService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new FeeStructureService();
    }

    public function index(): void
    {
        $this->success(['fee_structures' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['file'] ?? null);
        $result['ok'] ? $this->success(['fee_structure' => $this->service->find($result['id'])], 'Created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $_FILES['file'] ?? null);
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['fee_structure' => $this->service->find($result['id'])], 'Updated');
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Deleted') : $this->notFound();
    }
}
