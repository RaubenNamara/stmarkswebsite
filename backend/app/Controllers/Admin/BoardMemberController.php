<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\BoardMemberService;

class BoardMemberController extends Controller
{
    private BoardMemberService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new BoardMemberService();
    }

    public function index(): void
    {
        $this->success(['board_members' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input(), $_FILES['photo'] ?? null);
        $result['ok'] ? $this->success(['board_member' => $this->service->find($result['id'])], 'Board member created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input(), $_FILES['photo'] ?? null);
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['board_member' => $this->service->find($result['id'])], 'Board member updated');
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Board member deleted') : $this->notFound();
    }

    public function reorder(): void
    {
        $ids = $this->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            $this->validationError(['ids' => 'ids must be a non-empty array']);
            return;
        }
        $this->service->reorder(array_map('intval', $ids));
        $this->success(['board_members' => $this->service->all()], 'Board member order updated');
    }
}
