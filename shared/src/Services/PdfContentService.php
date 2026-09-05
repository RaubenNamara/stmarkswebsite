<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Model;
use StMarks\Shared\Support\Assets;
use StMarks\Shared\Support\PublicSiteBuildService;

/**
 * Shared logic for "a title plus one PDF" domains (FeeStructures, Performances) - upload/replace/
 * delete a single required PDF file.
 */
abstract class PdfContentService extends Service
{
    public function __construct(
        protected Model $model,
        protected string $uploadFeature,
        protected string $fileColumn,
        protected UploadService $uploadService = new UploadService()
    ) {
    }

    public function all(): array
    {
        return array_map([$this, 'withUrl'], $this->model->all([], ['created_at' => 'DESC']));
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ? $this->withUrl($row) : null;
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $pdfFile */
    public function create(array $data, ?array $pdfFile): array
    {
        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        if (!$pdfFile) {
            return ['ok' => false, 'errors' => [$this->fileColumn => 'Please upload a PDF file']];
        }

        $row = ['title' => htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8')];
        try {
            $row[$this->fileColumn] = $this->uploadService->storeDocument($this->uploadFeature, $pdfFile);
        } catch (UploadException $e) {
            return ['ok' => false, 'errors' => [$this->fileColumn => $e->getMessage()]];
        }

        $id = $this->model->create($row);
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create']];
        }
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data, ?array $pdfFile): array
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }

        $errors = $this->validate($data, ['title' => ['required', 'max:255']]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $row = ['title' => htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8')];
        if ($pdfFile) {
            try {
                $row[$this->fileColumn] = $this->uploadService->storeDocument($this->uploadFeature, $pdfFile);
            } catch (UploadException $e) {
                return ['ok' => false, 'errors' => [$this->fileColumn => $e->getMessage()]];
            }
            $this->uploadService->delete($existing[$this->fileColumn] ?? null);
        }

        $this->model->update($id, $row);
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function delete(int $id): bool
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return false;
        }
        $this->uploadService->delete($existing[$this->fileColumn] ?? null);
        $deleted = $this->model->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    protected function withUrl(array $row): array
    {
        $row['file_url'] = Assets::resolve($row[$this->fileColumn] ?? null);
        return $row;
    }
}
