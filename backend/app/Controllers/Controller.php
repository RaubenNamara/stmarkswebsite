<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers;

use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Validator;

/**
 * Base controller: request/response helpers and session-based auth accessors only. Deliberately
 * does NOT grow domain-specific helpers over time (eSpace's Controller.php picked up ~250 lines
 * of department-scoping business logic that belonged in a Service) - anything beyond "how do I
 * read this request" / "how do I answer it" belongs in a Service, not here.
 */
abstract class Controller
{
    protected array $requestData = [];
    protected array $queryParams = [];
    protected array $headers = [];

    public function __construct()
    {
        $this->parseRequest();
    }

    protected function parseRequest(): void
    {
        $this->queryParams = $_GET;

        if (function_exists('getallheaders')) {
            $this->headers = getallheaders() ?: [];
        } else {
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $header = str_replace('_', '-', substr($key, 5));
                    $this->headers[$header] = $value;
                }
            }
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'json')) {
            $raw = file_get_contents('php://input');
            $this->requestData = $raw ? (json_decode($raw, true) ?? []) : [];
        } else {
            $this->requestData = $_POST;
        }
    }

    protected function input(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->requestData : ($this->requestData[$key] ?? $default);
    }

    protected function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->queryParams : ($this->queryParams[$key] ?? $default);
    }

    protected function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function success(array $data = [], string $message = 'Success'): void
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data], 200);
    }

    public function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors) {
            $response['errors'] = $errors;
        }
        $this->json($response, $statusCode);
    }

    protected function validationError(array $errors, string $message = 'Validation failed'): void
    {
        $this->error($message, 422, $errors);
    }

    public function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, 401);
    }

    public function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }

    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404);
    }

    protected function serverError(string $message = 'Internal server error'): void
    {
        $this->error(Config::isDebug() ? $message : 'An error occurred', 500);
    }

    /**
     * Validates $this->requestData (or $data) against $rules using the app's one Validator.
     * Returns a field => message error map; empty means valid. Callers decide whether to call
     * validationError() themselves or push validation into a Service instead.
     */
    protected function validate(array $rules, ?array $data = null): array
    {
        return Validator::validate($data ?? $this->requestData, $rules);
    }

    protected function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Normalizes a multi-file input (e.g. <input type="file" name="images[]" multiple>, which PHP
     * exposes as $_FILES['images']['name'][0..n], ['tmp_name'][0..n], ...) into a plain list of
     * individual file arrays, the shape UploadService expects.
     *
     * @return array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    protected function normalizeFilesArray(string $key): array
    {
        if (!isset($_FILES[$key]) || !is_array($_FILES[$key]['name'] ?? null)) {
            return [];
        }

        $files = [];
        foreach ($_FILES[$key]['name'] as $i => $name) {
            if (($_FILES[$key]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'type' => $_FILES[$key]['type'][$i],
                'tmp_name' => $_FILES[$key]['tmp_name'][$i],
                'error' => $_FILES[$key]['error'][$i],
                'size' => $_FILES[$key]['size'][$i],
            ];
        }

        return $files;
    }

    protected function routeParam(string $key, mixed $default = null): mixed
    {
        global $routeParams;
        return $routeParams[$key] ?? $default;
    }

    protected function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
