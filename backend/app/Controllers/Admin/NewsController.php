<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\NewsService;

/**
 * Reference implementation for the admin CRUD pattern every other domain in Phase 3 follows:
 * thin controller, all rules/uploads/slugging in the Service, multipart POST for create/update
 * (PHP doesn't parse $_FILES on PUT/PATCH bodies, so update is POST too - see the route comment).
 */
class NewsController extends Controller
{
    private NewsService $newsService;

    public function __construct()
    {
        parent::__construct();
        $this->newsService = new NewsService();
    }

    public function index(): void
    {
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));
        $search = (string) $this->query('search', '');

        $this->success($this->newsService->paginate($page, $limit, $search));
    }

    public function show(string $id): void
    {
        $news = $this->newsService->find((int) $id);
        if (!$news) {
            $this->notFound('News item not found');
            return;
        }

        $this->success(['news' => $news]);
    }

    public function store(): void
    {
        $result = $this->newsService->create($this->input(), $_FILES['image'] ?? null);

        if (!$result['ok']) {
            $this->validationError($result['errors']);
            return;
        }

        $this->success(['news' => $this->newsService->find($result['id'])], 'News item created');
    }

    /** POST (not PUT) - see class docblock. */
    public function update(string $id): void
    {
        $result = $this->newsService->update((int) $id, $this->input(), $_FILES['image'] ?? null);

        if (!$result['ok']) {
            $status = isset($result['errors']['general']) ? 404 : 422;
            $this->error($result['errors']['general'] ?? 'Validation failed', $status, $result['errors']);
            return;
        }

        $this->success(['news' => $this->newsService->find($result['id'])], 'News item updated');
    }

    public function destroy(string $id): void
    {
        if (!$this->newsService->delete((int) $id)) {
            $this->notFound('News item not found');
            return;
        }

        $this->success([], 'News item deleted');
    }
}
