<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Model;
use StMarks\Shared\Support\Assets;
use StMarks\Shared\Support\PublicSiteBuildService;

/**
 * Shared create/update/delete/list logic for the ~7 domains that are just "a flat table with an
 * optional image and an optional video" (CoCurricular, GirlBoyTalk, Mentorship, Chaplaincy,
 * InspirationNight, ChristmasCantata, StudentLeadership) - genuinely identical behavior, not just
 * similar, so extracted for real (see the plan's caution against premature abstraction: this is
 * the opposite case, exact duplication across many call sites). Per-domain differences (extra
 * fields like `date`/`speaker`/`caption`, validation rules, column names) are handled via the
 * constructor config and $data flowing through untouched - subclasses stay tiny.
 */
abstract class ImageVideoContentService extends Service
{
    public function __construct(
        protected Model $model,
        protected string $uploadFeature,
        protected array $requiredFields = ['title' => ['required', 'max:255']],
        protected string $imageColumn = 'image',
        protected string $videoColumn = 'video',
        protected UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(array $orderBy = ['created_at' => 'DESC']): array
    {
        return array_map([$this, 'withUrls'], $this->model->all([], $orderBy));
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ? $this->withUrls($row) : null;
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $imageFile
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $videoFile
     */
    public function create(array $data, ?array $imageFile, ?array $videoFile): array
    {
        $errors = $this->validate($data, $this->requiredFields);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->sanitize($data);
        $uploadError = $this->applyUploads($data, $imageFile, $videoFile);
        if ($uploadError !== null) {
            return $uploadError;
        }

        $id = $this->model->create($data);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create']];
        }
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data, ?array $imageFile, ?array $videoFile): array
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }

        $errors = $this->validate($data, $this->requiredFields);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->sanitize($data);
        $uploadError = $this->applyUploads($data, $imageFile, $videoFile);
        if ($uploadError !== null) {
            return $uploadError;
        }
        if ($imageFile) {
            $this->uploadService->delete($existing[$this->imageColumn] ?? null);
        }
        if ($videoFile) {
            $this->uploadService->delete($existing[$this->videoColumn] ?? null);
        }

        $this->model->update($id, $data);
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return false;
        }
        $this->uploadService->delete($existing[$this->imageColumn] ?? null);
        $this->uploadService->delete($existing[$this->videoColumn] ?? null);
        $deleted = $this->model->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    /** @return array{ok:false,errors:array}|null null means no error */
    private function applyUploads(array &$data, ?array $imageFile, ?array $videoFile): ?array
    {
        if ($imageFile) {
            try {
                $data[$this->imageColumn] = $this->uploadService->storeImage($this->uploadFeature, $imageFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => [$this->imageColumn => $e->getMessage()]];
            }
        }
        if ($videoFile) {
            try {
                $data[$this->videoColumn] = $this->uploadService->storeVideo($this->uploadFeature, $videoFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => [$this->videoColumn => $e->getMessage()]];
            }
        }
        return null;
    }

    protected function withUrls(array $row): array
    {
        $row[$this->imageColumn . '_url'] = Assets::resolve($row[$this->imageColumn] ?? null);
        $row[$this->videoColumn . '_url'] = Assets::resolve($row[$this->videoColumn] ?? null);
        return $row;
    }
}
