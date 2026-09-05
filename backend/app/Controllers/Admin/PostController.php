<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\PostService;

class PostController extends Controller
{
    private PostService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PostService();
    }

    public function index(): void
    {
        $this->success(['posts' => $this->service->all()]);
    }

    public function show(string $id): void
    {
        $row = $this->service->find((int) $id);
        $row ? $this->success(['post' => $row]) : $this->notFound();
    }

    public function store(): void
    {
        $result = $this->service->create($this->input());
        $result['ok'] ? $this->success(['post' => $this->service->find($result['id'])], 'Post created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input());
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['post' => $this->service->find($result['id'])], 'Post updated');
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Post deleted') : $this->notFound();
    }
}
