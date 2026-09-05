<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Club;
use StMarks\Shared\Models\ClubImage;
use StMarks\Shared\Support\Assets;
use StMarks\Shared\Support\PublicSiteBuildService;

class ClubService extends Service
{
    private const UPLOAD_FEATURE = 'clubs';

    public function __construct(
        private Club $clubModel = new Club(),
        private ClubImage $imageModel = new ClubImage(),
        private SlugService $slugService = new SlugService(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(): array
    {
        return array_map(fn ($club) => $this->withImages($club), $this->clubModel->all([], ['created_at' => 'DESC']));
    }

    public function find(int $id): ?array
    {
        $club = $this->clubModel->find($id);
        return $club ? $this->withImages($club) : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $club = $this->clubModel->findBySlug($slug);
        return $club ? $this->withImages($club) : null;
    }

    /** @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $imageFiles */
    public function create(array $data, array $imageFiles): array
    {
        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $title = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        $id = $this->clubModel->create([
            'title' => $title,
            'slug' => $this->slugService->unique($this->clubModel, $title),
            'content' => trim($data['content'] ?? ''),
        ]);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create club']];
        }

        $uploadError = $this->storeImages((int) $id, $imageFiles);
        if ($uploadError !== null) {
            return $uploadError;
        }

        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    /** @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $imageFiles */
    public function update(int $id, array $data, array $imageFiles): array
    {
        $existing = $this->clubModel->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Club not found']];
        }

        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $title = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        $update = ['title' => $title, 'content' => trim($data['content'] ?? '')];
        if ($title !== $existing['title']) {
            $update['slug'] = $this->slugService->unique($this->clubModel, $title, $id);
        }
        $this->clubModel->update($id, $update);

        $uploadError = $this->storeImages($id, $imageFiles);
        if ($uploadError !== null) {
            return $uploadError;
        }

        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
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

    public function delete(int $id): bool
    {
        if (!$this->clubModel->exists($id)) {
            return false;
        }
        foreach ($this->imageModel->forClub($id) as $image) {
            $this->uploadService->delete($image['image_path'] ?? null);
        }
        // club_images has ON DELETE CASCADE on club_id, so this also removes the rows.
        $deleted = $this->clubModel->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    /** @return array{ok:false,errors:array}|null */
    private function storeImages(int $clubId, array $imageFiles): ?array
    {
        foreach ($imageFiles as $file) {
            try {
                $path = $this->uploadService->storeImage(self::UPLOAD_FEATURE, $file);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => ['images' => $e->getMessage()]];
            }
            $this->imageModel->create(['club_id' => $clubId, 'image_path' => $path]);
        }
        return null;
    }

    private function withImages(array $club): array
    {
        $club['images'] = array_map(
            fn ($img) => ['id' => (int) $img['id'], 'image_url' => Assets::resolve($img['image_path'])],
            $this->imageModel->forClub((int) $club['id'])
        );
        return $club;
    }
}
