<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Media;
use StMarks\Shared\Support\Assets;

/**
 * Homepage media items - one of: an uploaded image, an uploaded video, or an external video URL
 * (e.g. YouTube link). No update in the old app (index/store/destroy only), so none here either.
 */
class MediaService extends Service
{
    private const UPLOAD_FEATURE = 'media';

    public function __construct(
        private Media $model = new Media(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(): array
    {
        return array_map([$this, 'withUrl'], $this->model->all([], ['created_at' => 'DESC']));
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $file */
    public function create(array $data, ?array $file): array
    {
        $videoUrl = trim((string) ($data['video_url'] ?? ''));

        if (!$file && $videoUrl === '') {
            return ['ok' => false, 'errors' => ['file' => 'Please upload a file or provide a video URL.']];
        }

        $row = [
            'title' => isset($data['title']) ? htmlspecialchars(trim((string) $data['title']), ENT_QUOTES, 'UTF-8') : null,
            'is_active' => 1,
        ];

        if ($file) {
            $isVideo = str_starts_with($file['type'] ?? '', 'video/');
            try {
                $row['file_path'] = $isVideo
                    ? $this->uploadService->storeVideo(self::UPLOAD_FEATURE, $file)
                    : $this->uploadService->storeImage(self::UPLOAD_FEATURE, $file);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['file' => $e->getMessage()]];
            }
            $row['type'] = $isVideo ? 'video' : 'image';
        } else {
            $row['video_url'] = filter_var($videoUrl, FILTER_VALIDATE_URL) ? $videoUrl : null;
            if (!$row['video_url']) {
                return ['ok' => false, 'errors' => ['video_url' => 'Please provide a valid URL']];
            }
            $row['type'] = 'link';
        }

        $id = $this->model->create($row);
        return $id === false ? ['ok' => false, 'errors' => ['general' => 'Failed to create media item']] : ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return false;
        }
        $this->uploadService->delete($existing['file_path'] ?? null);
        return $this->model->delete($id);
    }

    private function withUrl(array $row): array
    {
        $row['file_url'] = Assets::resolve($row['file_path'] ?? null);
        return $row;
    }
}
