<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Slide;
use StMarks\Shared\Support\Assets;

/**
 * Homepage carousel slides - ported from the old app's SlideController (which lived oddly
 * outside /admin) into a proper admin domain. One of image/video is required per slide;
 * `order` is assigned as count()+1 like the original, `is_active` always true (no UI ever
 * toggled it off in the old app either).
 */
class SlideService extends Service
{
    private const UPLOAD_FEATURE = 'slides';

    public function __construct(
        private Slide $slideModel = new Slide(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function allWithUrls(): array
    {
        return array_map([$this, 'withUrls'], $this->slideModel->allOrdered());
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $imageFile
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $videoFile
     */
    public function create(array $data, ?array $imageFile, ?array $videoFile): array
    {
        if (!$imageFile && !$videoFile) {
            return ['ok' => false, 'errors' => ['file' => 'Please upload an image or a video.']];
        }

        $slideData = [
            'title' => isset($data['title']) ? htmlspecialchars(trim((string) $data['title']), ENT_QUOTES, 'UTF-8') : null,
            'caption' => isset($data['caption']) ? htmlspecialchars(trim((string) $data['caption']), ENT_QUOTES, 'UTF-8') : null,
            'order' => $this->slideModel->count() + 1,
            'is_active' => 1,
        ];

        try {
            if ($videoFile) {
                $slideData['video_path'] = $this->uploadService->storeVideo(self::UPLOAD_FEATURE, $videoFile, 52_428_800);
                $slideData['type'] = 'video';
            } else {
                $slideData['image_path'] = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $imageFile, 20_971_520);
                $slideData['type'] = 'image';
            }
        } catch (UploadException $e) {
            return ['ok' => false, 'errors' => ['file' => $e->getMessage()]];
        }

        $id = $this->slideModel->create($slideData);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create slide']];
        }

        return ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $slide = $this->slideModel->find($id);
        if (!$slide) {
            return false;
        }

        $this->uploadService->delete($slide['image_path'] ?? null);
        $this->uploadService->delete($slide['video_path'] ?? null);

        return $this->slideModel->delete($id);
    }

    private function withUrls(array $slide): array
    {
        return [
            'id' => (int) $slide['id'],
            'title' => $slide['title'],
            'caption' => $slide['caption'],
            'type' => $slide['type'],
            'image_url' => Assets::resolve($slide['image_path'] ?: null),
            'video_url' => Assets::resolve($slide['video_path'] ?: null),
        ];
    }
}
