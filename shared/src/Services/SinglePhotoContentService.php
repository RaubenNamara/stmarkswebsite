<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Model;
use StMarks\Shared\Support\Assets;

/**
 * Shared logic for domains that are a flat table with a single optional photo and no video
 * (BoardMember, HighAchiever, Staff) - same rationale as ImageVideoContentService, just the
 * simpler one-file shape.
 */
abstract class SinglePhotoContentService extends Service
{
    public function __construct(
        protected Model $model,
        protected string $uploadFeature,
        protected array $requiredFields,
        protected string $photoColumn = 'photo',
        protected UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(array $orderBy = ['created_at' => 'DESC']): array
    {
        return array_map([$this, 'withUrl'], $this->model->all([], $orderBy));
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ? $this->withUrl($row) : null;
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $photoFile */
    public function create(array $data, ?array $photoFile): array
    {
        $errors = $this->validate($data, $this->requiredFields);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = $this->sanitize($data);
        if ($photoFile) {
            try {
                $data[$this->photoColumn] = $this->uploadService->storeImage($this->uploadFeature, $photoFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => [$this->photoColumn => $e->getMessage()]];
            }
        }

        $id = $this->model->create($data);
        return $id === false ? ['ok' => false, 'errors' => ['general' => 'Failed to create']] : ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data, ?array $photoFile): array
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
        if ($photoFile) {
            try {
                $data[$this->photoColumn] = $this->uploadService->storeImage($this->uploadFeature, $photoFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => [$this->photoColumn => $e->getMessage()]];
            }
            $this->uploadService->delete($existing[$this->photoColumn] ?? null);
        }

        $this->model->update($id, $data);
        return ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return false;
        }
        $this->uploadService->delete($existing[$this->photoColumn] ?? null);
        return $this->model->delete($id);
    }

    protected function withUrl(array $row): array
    {
        $row[$this->photoColumn . '_url'] = Assets::resolve($row[$this->photoColumn] ?? null);
        return $row;
    }
}
