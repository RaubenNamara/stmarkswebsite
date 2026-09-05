<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

use StMarks\Backend\Support\MiddlewareRegistry;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Logger;
use StMarks\Shared\Support\Router;

Config::load();

error_reporting(E_ALL);
ini_set('display_errors', Config::isDebug() ? '1' : '0');

// Convert PHP warnings/notices into exceptions so they funnel through the one exception handler
// below, instead of leaking raw HTML/text into what should always be a JSON response.
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (\Throwable $e): void {
    Logger::error('Uncaught ' . get_class($e) . ': ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode([
        'success' => false,
        'message' => Config::isDebug() ? $e->getMessage() : 'An unexpected error occurred',
    ]);
});

// Session bootstrap - PHP session-cookie auth, not JWT.
if (session_status() === PHP_SESSION_NONE) {
    $isProduction = Config::get('APP_ENV') === 'production';
    session_set_cookie_params([
        'lifetime' => (int) Config::get('SESSION_LIFETIME', 7200),
        'path' => '/',
        'httponly' => true,
        'secure' => $isProduction,
        'samesite' => $isProduction ? 'Strict' : 'Lax',
    ]);
    session_start();
}

// CORS - only meaningful when the admin SPA is served from a different origin than this API
// (e.g. Vite's dev server without its proxy configured). Same-origin production deploys don't
// depend on this.
$frontendUrl = Config::get('ADMIN_FRONTEND_URL');
if ($frontendUrl) {
    header('Access-Control-Allow-Origin: ' . $frontendUrl);
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

Router::setUriAnchor('/api/');
Router::setMiddlewareResolver([MiddlewareRegistry::class, 'resolve']);
Router::setNotFoundHandler(function (string $method, string $uri): void {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    exit;
});
Router::setErrorHandler(function (\Throwable $e): void {
    Logger::error('Route handler error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => Config::isDebug() ? $e->getMessage() : 'An unexpected error occurred',
    ]);
    exit;
});

require __DIR__ . '/../routes/api.php';

Router::dispatch();
