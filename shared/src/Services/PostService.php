<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Post;
use StMarks\Shared\Support\PublicSiteBuildService;

class PostService extends Service
{
    public function __construct(
        private Post $postModel = new Post(),
        private SlugService $slugService = new SlugService()
    ) {
    }

    public function all(): array
    {
        return $this->postModel->all([], ['created_at' => 'DESC']);
    }

    public function published(): array
    {
        return $this->postModel->all(['is_published' => 1], ['created_at' => 'DESC']);
    }

    public function find(int $id): ?array
    {
        return $this->postModel->find($id);
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $post = $this->postModel->first(['slug' => $slug]);
        return ($post && $post['is_published']) ? $post : null;
    }

    public function create(array $data): array
    {
        $errors = $this->validate($data, ['title' => ['required', 'max:255'], 'content' => ['required']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        // title is plain text (escaped); content is trusted rich HTML from the admin WYSIWYG
        // editor (Phase 5) - see NewsService's identical sanitizePlainFields() rationale.
        $data['title'] = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        $data['content'] = trim($data['content']);
        $data['slug'] = $this->slugService->unique($this->postModel, $data['title']);
        $data['is_published'] = isset($data['is_published']) ? (int) (bool) $data['is_published'] : 1;

        $id = $this->postModel->create($data);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create post']];
        }
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->postModel->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Post not found']];
        }

        $errors = $this->validate($data, ['title' => ['required', 'max:255'], 'content' => ['required']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data['title'] = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        $data['content'] = trim($data['content']);
        if ($data['title'] !== $existing['title']) {
            $data['slug'] = $this->slugService->unique($this->postModel, $data['title'], $id);
        }
        $data['is_published'] = isset($data['is_published']) ? (int) (bool) $data['is_published'] : (int) $existing['is_published'];

        $this->postModel->update($id, $data);
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $deleted = $this->postModel->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }
}
