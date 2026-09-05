<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\News;
use StMarks\Shared\Support\Assets;

class NewsService extends Service
{
    private const UPLOAD_FEATURE = 'news';

    public function __construct(
        private News $newsModel = new News(),
        private SlugService $slugService = new SlugService(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function paginate(int $page, int $limit, string $search = ''): array
    {
        $result = $this->newsModel->paginate($page, $limit, array_filter(['search' => $search]));
        $result['data'] = array_map([$this, 'withImageUrl'], $result['data']);
        return $result;
    }

    public function published(int $limit = 0, int $offset = 0): array
    {
        return array_map([$this, 'withImageUrl'], $this->newsModel->published($limit, $offset));
    }

    public function findBySlug(string $slug): ?array
    {
        $news = $this->newsModel->findBySlug($slug);
        return $news ? $this->withImageUrl($news) : null;
    }

    public function find(int $id): ?array
    {
        $news = $this->newsModel->find($id);
        return $news ? $this->withImageUrl($news) : null;
    }

    private function withImageUrl(array $news): array
    {
        $news['image_url'] = Assets::resolve($news['image_path'] ?? null);
        return $news;
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $imageFile
     * @return array{ok: bool, id?: int, errors?: array}
     */
    public function create(array $data, ?array $imageFile): array
    {
        $errors = $this->validate($data, [
            'title' => ['required', 'max:255'],
            'content' => ['required'],
        ]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->sanitizePlainFields($data);
        $data['slug'] = $this->slugService->unique($this->newsModel, $data['title']);
        $data['is_published'] = isset($data['is_published']) ? (int) (bool) $data['is_published'] : 1;
        $data['published_at'] = $data['is_published'] ? date('Y-m-d H:i:s') : null;

        if ($imageFile) {
            try {
                $data['image_path'] = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $imageFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['image' => $e->getMessage()]];
            }
        }

        $id = $this->newsModel->create($data);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create news item']];
        }

        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data, ?array $imageFile): array
    {
        $existing = $this->newsModel->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'News item not found']];
        }

        $errors = $this->validate($data, [
            'title' => ['required', 'max:255'],
            'content' => ['required'],
        ]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->sanitizePlainFields($data);
        if ($data['title'] !== $existing['title']) {
            $data['slug'] = $this->slugService->unique($this->newsModel, $data['title'], $id);
        }
        $data['is_published'] = isset($data['is_published']) ? (int) (bool) $data['is_published'] : (int) $existing['is_published'];
        if ($data['is_published'] && !$existing['is_published']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        if ($imageFile) {
            try {
                $data['image_path'] = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $imageFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['image' => $e->getMessage()]];
            }
            $this->uploadService->delete($existing['image_path'] ?? null);
        }

        $this->newsModel->update($id, $data);

        return ['ok' => true, 'id' => $id];
    }

    /**
     * Escapes title/excerpt (plain text) but leaves `content` as-is - it's trusted rich HTML from
     * the admin WYSIWYG editor, meant to render unescaped on the public site. Blanket-sanitizing
     * every string field (Service::sanitize()'s default behaviour) would htmlspecialchars the
     * HTML markup itself, breaking every heading/link/image the editor produced.
     */
    private function sanitizePlainFields(array $data): array
    {
        foreach (['title', 'excerpt'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = htmlspecialchars(trim($data[$field]), ENT_QUOTES, 'UTF-8');
            }
        }
        if (isset($data['content']) && is_string($data['content'])) {
            $data['content'] = trim($data['content']);
        }

        return $data;
    }

    public function delete(int $id): bool
    {
        $existing = $this->newsModel->find($id);
        if (!$existing) {
            return false;
        }

        $this->uploadService->delete($existing['image_path'] ?? null);
        return $this->newsModel->delete($id);
    }
}
