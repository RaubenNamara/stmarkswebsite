<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\CampusVoice;
use StMarks\Shared\Support\Assets;

/**
 * Ports the old Laravel CampusVoice model's boot-time slug generation, auto-summary, and
 * reading-time/view-increment logic into a Service (that model had real business logic baked
 * into Eloquent hooks/accessors - see the plan's note to port it here rather than the old
 * pattern of logic living in the model itself).
 */
class CampusVoiceService extends Service
{
    private const UPLOAD_FEATURE = 'campus-voices';

    public function __construct(
        private CampusVoice $model = new CampusVoice(),
        private SlugService $slugService = new SlugService(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function paginate(int $page, int $limit, string $search = ''): array
    {
        $result = $this->model->paginate($page, $limit, array_filter(['search' => $search]));
        $result['data'] = array_map([$this, 'withExtras'], $result['data']);
        return $result;
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ? $this->withExtras($row) : null;
    }

    public function published(): array
    {
        return array_map([$this, 'withExtras'], $this->model->all(['status' => 'published'], ['published_at' => 'DESC']));
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->model->findBySlug($slug);
        if (!$row || $row['status'] !== 'published') {
            return null;
        }
        $this->model->incrementViews((int) $row['id']);
        $row['views'] = (int) $row['views'] + 1;
        return $this->withExtras($row);
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $imageFile */
    public function create(array $data, ?array $imageFile): array
    {
        $errors = $this->validate($data, ['student_name' => ['required', 'max:255'], 'title' => ['required', 'max:255'], 'content' => ['required']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $row = $this->prepareRow($data);
        $row['slug'] = $this->slugService->unique($this->model, $row['title']);
        $row['views'] = 0;

        if ($imageFile) {
            try {
                $row['featured_image'] = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $imageFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['featured_image' => $e->getMessage()]];
            }
        }

        $id = $this->model->create($row);
        return $id === false ? ['ok' => false, 'errors' => ['general' => 'Failed to create']] : ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data, ?array $imageFile): array
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }

        $errors = $this->validate($data, ['student_name' => ['required', 'max:255'], 'title' => ['required', 'max:255'], 'content' => ['required']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $row = $this->prepareRow($data);
        if ($row['title'] !== $existing['title']) {
            $row['slug'] = $this->slugService->unique($this->model, $row['title'], $id);
        }

        if ($imageFile) {
            try {
                $row['featured_image'] = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $imageFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['featured_image' => $e->getMessage()]];
            }
            $this->uploadService->delete($existing['featured_image'] ?? null);
        }

        $this->model->update($id, $row);
        return ['ok' => true, 'id' => $id];
    }

    public function toggleFeatured(int $id): array
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }
        $this->model->update($id, ['featured' => $existing['featured'] ? 0 : 1]);
        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return false;
        }
        $this->uploadService->delete($existing['featured_image'] ?? null);
        return $this->model->delete($id);
    }

    private function prepareRow(array $data): array
    {
        $title = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        $content = trim($data['content']); // trusted rich HTML from the admin editor, not escaped

        $summary = isset($data['summary']) && trim((string) $data['summary']) !== ''
            ? htmlspecialchars(trim($data['summary']), ENT_QUOTES, 'UTF-8')
            : $this->autoSummary($content);

        $requestedStatus = $data['status'] ?? 'published';
        $status = in_array($requestedStatus, ['draft', 'published'], true) ? $requestedStatus : 'published';

        return [
            'student_name' => htmlspecialchars(trim($data['student_name']), ENT_QUOTES, 'UTF-8'),
            'title' => $title,
            'content' => $content,
            'summary' => $summary,
            'author_bio' => isset($data['author_bio']) ? htmlspecialchars(trim($data['author_bio']), ENT_QUOTES, 'UTF-8') : null,
            'category' => isset($data['category']) ? htmlspecialchars(trim($data['category']), ENT_QUOTES, 'UTF-8') : null,
            'author' => isset($data['author']) ? htmlspecialchars(trim($data['author']), ENT_QUOTES, 'UTF-8') : null,
            'featured' => isset($data['featured']) ? (int) (bool) $data['featured'] : 0,
            'status' => $status,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
        ];
    }

    private function autoSummary(string $content): string
    {
        $plain = trim(strip_tags($content));
        return mb_strlen($plain) > 150 ? mb_substr($plain, 0, 150) . '...' : $plain;
    }

    private function withExtras(array $row): array
    {
        $row['featured_image_url'] = Assets::resolve($row['featured_image'] ?? null);
        $plainContent = strip_tags($row['content'] ?? '');
        $wordCount = str_word_count($plainContent);
        $row['reading_time'] = max(1, (int) ceil($wordCount / 200));
        return $row;
    }
}
