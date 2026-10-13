<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Media;
use StMarks\Shared\Support\Assets;

/**
 * Homepage media items - one of: an uploaded image, an uploaded video, or an external video URL
 * (e.g. YouTube link). Each can carry a title and a short caption, shown as overlay text in the
 * homepage slideshow. The old app had no update (index/store/destroy only) - update() below is a
 * deliberate addition so an existing item's title/caption can be edited without deleting and
 * re-uploading the file; it intentionally never touches file_path/video_url/type.
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
        // id DESC as a tiebreaker: several items from the same multi-image upload can share the
        // same created_at second, and without a stable secondary key their relative order isn't
        // guaranteed - the homepage slideshow reverses this list to play them back in upload order.
        return array_map([$this, 'withUrl'], $this->model->all([], ['created_at' => 'DESC', 'id' => 'DESC']));
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $file */
    public function create(array $data, ?array $file): array
    {
        $videoUrl = trim((string) ($data['video_url'] ?? ''));

        if (!$file && $videoUrl === '') {
            return ['ok' => false, 'errors' => ['file' => 'Please upload a file or provide a video URL.']];
        }

        $caption = trim((string) ($data['caption'] ?? ''));

        $row = [
            'title' => isset($data['title']) ? htmlspecialchars(trim((string) $data['title']), ENT_QUOTES, 'UTF-8') : null,
            'caption' => $caption !== '' ? htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') : null,
            'is_active' => 1,
        ];

        if ($file) {
            $isVideo = str_starts_with($file['type'] ?? '', 'video/');
            try {
                // 20MB, not the default 5MB - matches SlideService's own homepage-slideshow image
                // cap. These play full-bleed like hero slides, and real camera/phone photos
                // routinely exceed 5MB.
                $row['file_path'] = $isVideo
                    ? $this->uploadService->storeVideo(self::UPLOAD_FEATURE, $file)
                    : $this->uploadService->storeImage(self::UPLOAD_FEATURE, $file, 20_971_520);
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

    public function update(int $id, array $data): array
    {
        if (!$this->model->exists($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Media item not found']];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $caption = trim((string) ($data['caption'] ?? ''));

        $this->model->update($id, [
            'title' => $title !== '' ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : null,
            'caption' => $caption !== '' ? htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') : null,
        ]);

        return ['ok' => true, 'id' => $id];
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
