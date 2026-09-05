<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\JobApplication;
use StMarks\Shared\Support\Assets;

/** Handles both the public /apply form submission and the admin view/delete side. */
class JobApplicationService extends Service
{
    private const UPLOAD_FEATURE = 'applications';

    public function __construct(
        private JobApplication $model = new JobApplication(),
        private UploadService $uploadService = new UploadService()
    ) {
    }

    /** @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $cvFile */
    public function submit(array $data, ?array $cvFile): array
    {
        $errors = $this->validate($data, [
            'full_name' => ['required', 'max:255'],
            'contact' => ['required', 'max:255'],
            'email' => ['required', 'email'],
            'address' => ['required'],
            'position' => ['required', 'max:255'],
        ]);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        if (!$cvFile) {
            return ['ok' => false, 'errors' => ['file' => 'Please attach your CV as a PDF']];
        }

        $row = $this->sanitize([
            'full_name' => $data['full_name'],
            'contact' => $data['contact'],
            'email' => $data['email'],
            'address' => $data['address'],
            'position' => $data['position'],
        ]);

        try {
            $row['file_path'] = $this->uploadService->storeDocument(self::UPLOAD_FEATURE, $cvFile, 5_242_880);
        } catch (UploadException $e) {
            return ['ok' => false, 'errors' => ['file' => $e->getMessage()]];
        }

        $id = $this->model->create($row);
        return $id === false ? ['ok' => false, 'errors' => ['general' => 'Failed to submit application']] : ['ok' => true, 'id' => $id];
    }

    public function paginate(int $page, int $limit, string $search = ''): array
    {
        $result = $this->model->paginate($page, $limit, array_filter(['search' => $search]));
        $result['data'] = array_map([$this, 'withUrl'], $result['data']);
        return $result;
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
