<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Config\Config;

/**
 * The one upload mechanism for the whole app - the old Laravel app used three different
 * techniques (Storage facade, raw move() to base_path('../storage/...'), raw move() to
 * $_SERVER['DOCUMENT_ROOT']) across ~15 controllers; everything here goes through this instead.
 *
 * Writes into <repo root>/storage/uploads/<feature>/<generated-name> (physically one directory,
 * junctioned into both backend/public/uploads and public-site/public/uploads - see the plan's
 * upload convention), returns a root-relative URL persisted verbatim to the DB.
 */
class UploadService extends Service
{
    private const IMAGE_MIME_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    private const DOCUMENT_MIME_EXT = [
        'application/pdf' => 'pdf',
    ];

    private const VIDEO_MIME_EXT = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/avi' => 'avi',
    ];

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file A $_FILES[...] entry
     * @throws UploadException
     */
    public function storeImage(string $feature, array $file, int $maxBytes = 5_242_880): string
    {
        $path = $this->store($feature, $file, self::IMAGE_MIME_EXT, $maxBytes);

        if (@getimagesize($this->absolutePath($path)) === false) {
            @unlink($this->absolutePath($path));
            throw new UploadException('The uploaded file is not a valid image');
        }

        return $path;
    }

    /** @throws UploadException */
    public function storeDocument(string $feature, array $file, int $maxBytes = 20_971_520): string
    {
        return $this->store($feature, $file, self::DOCUMENT_MIME_EXT, $maxBytes);
    }

    /** @throws UploadException */
    public function storeVideo(string $feature, array $file, int $maxBytes = 52_428_800): string
    {
        return $this->store($feature, $file, self::VIDEO_MIME_EXT, $maxBytes);
    }

    /**
     * @param array<string, string> $allowedMimeExt MIME type => file extension
     * @throws UploadException
     */
    private function store(string $feature, array $file, array $allowedMimeExt, int $maxBytes): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new UploadException('File upload failed');
        }

        if (($file['size'] ?? 0) > $maxBytes) {
            throw new UploadException('File exceeds the maximum allowed size of ' . round($maxBytes / 1_048_576, 1) . 'MB');
        }

        $tmpName = $file['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName) ?: '';
        finfo_close($finfo);

        if (!isset($allowedMimeExt[$mime])) {
            throw new UploadException('File type not allowed');
        }

        $extension = $allowedMimeExt[$mime];
        $filename = $feature . '_' . bin2hex(random_bytes(12)) . '.' . $extension;

        $featureDir = Config::getUploadsPath($feature);
        if (!is_dir($featureDir) && !@mkdir($featureDir, 0755, true) && !is_dir($featureDir)) {
            throw new UploadException('Could not create upload directory');
        }

        $destination = $featureDir . DIRECTORY_SEPARATOR . $filename;
        $moved = is_uploaded_file($tmpName)
            ? move_uploaded_file($tmpName, $destination)
            : rename($tmpName, $destination); // CLI/test contexts don't set is_uploaded_file()

        if (!$moved) {
            throw new UploadException('Could not save uploaded file');
        }

        return '/uploads/' . $feature . '/' . $filename;
    }

    /** Deletes a previously-stored file given the root-relative URL returned by store*(). */
    public function delete(?string $relativeUrl): void
    {
        if (!$relativeUrl || !str_starts_with($relativeUrl, '/uploads/')) {
            return;
        }

        $path = Config::getUploadsPath(substr($relativeUrl, strlen('/uploads/')));
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function absolutePath(string $relativeUrl): string
    {
        return Config::getUploadsPath(substr($relativeUrl, strlen('/uploads/')));
    }
}
