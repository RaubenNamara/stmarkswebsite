<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\GalleryEvent;
use StMarks\Shared\Models\GalleryImage;
use StMarks\Shared\Support\Assets;
use StMarks\Shared\Support\PublicSiteBuildService;

class GalleryService extends Service
{
    private const UPLOAD_FEATURE = 'gallery';

    public function __construct(
        private GalleryEvent $eventModel = new GalleryEvent(),
        private GalleryImage $imageModel = new GalleryImage(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(): array
    {
        $events = $this->eventModel->all([], ['created_at' => 'DESC']);
        return array_map(fn ($event) => $this->withImages($event), $events);
    }

    /** @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $imageFiles */
    public function createEvent(array $data, array $imageFiles): array
    {
        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->eventModel->create(['title' => htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8')]);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create event']];
        }

        $uploadError = $this->storeImages((int) $id, $imageFiles);
        if ($uploadError !== null) {
            return $uploadError;
        }

        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function updateEvent(int $id, array $data): array
    {
        if (!$this->eventModel->exists($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Event not found']];
        }
        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $this->eventModel->update($id, ['title' => htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8')]);
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    /** @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $imageFiles */
    public function addImages(int $eventId, array $imageFiles): array
    {
        if (!$this->eventModel->exists($eventId)) {
            return ['ok' => false, 'errors' => ['general' => 'Event not found']];
        }
        $uploadError = $this->storeImages($eventId, $imageFiles);
        if ($uploadError !== null) {
            return $uploadError;
        }
        PublicSiteBuildService::trigger();
        return ['ok' => true];
    }

    public function deleteImage(int $imageId): bool
    {
        $image = $this->imageModel->find($imageId);
        if (!$image) {
            return false;
        }
        $this->uploadService->delete($image['image_path'] ?? null);
        $deleted = $this->imageModel->delete($imageId);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    public function deleteEvent(int $id): bool
    {
        if (!$this->eventModel->exists($id)) {
            return false;
        }
        foreach ($this->imageModel->forEvent($id) as $image) {
            $this->uploadService->delete($image['image_path'] ?? null);
        }
        // gallery_images has ON DELETE CASCADE on gallery_event_id, so this also removes the rows.
        $deleted = $this->eventModel->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    /** @return array{ok:false,errors:array}|null */
    private function storeImages(int $eventId, array $imageFiles): ?array
    {
        foreach ($imageFiles as $file) {
            try {
                $path = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $file);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['images' => $e->getMessage()]];
            }
            $this->imageModel->create(['gallery_event_id' => $eventId, 'image_path' => $path]);
        }
        return null;
    }

    private function withImages(array $event): array
    {
        $event['images'] = array_map(
            fn ($img) => ['id' => (int) $img['id'], 'image_url' => Assets::resolve($img['image_path'])],
            $this->imageModel->forEvent((int) $event['id'])
        );
        return $event;
    }
}
