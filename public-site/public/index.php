<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Logger;
use StMarks\Shared\Support\Router;

Config::load();
View::setTemplatesDir(dirname(__DIR__) . '/templates');

error_reporting(E_ALL);
ini_set('display_errors', Config::isDebug() ? '1' : '0');

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
    }

    View::render('errors/500', [], meta: ['title' => 'Something went wrong']);
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

Router::setUriAnchor(null);
// Empty by default (production: the vhost's DocumentRoot IS this public/ dir, so REQUEST_URI is
// already the route path). Set PUBLIC_SITE_BASE_PATH during the parallel-build period when this
// app is reached through a project-root rewrite instead (see routes/web.php's neighboring
// project-root .htaccess) - e.g. '/stmarkswebsite' for the local unified setup.
Router::setExtraBasePathPrefix(Config::get('PUBLIC_SITE_BASE_PATH') ?: null);
Router::setMiddlewareResolver(fn () => null); // public pages need no middleware today
Router::setNotFoundHandler(function (string $method, string $uri): void {
    http_response_code(404);
    View::render('errors/404', [], meta: ['title' => 'Page not found']);
    exit;
});
Router::setErrorHandler(function (\Throwable $e): void {
    Logger::error('Route handler error: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    http_response_code(500);
    View::render('errors/500', [], meta: ['title' => 'Something went wrong']);
    exit;
});

require __DIR__ . '/../routes/web.php';

Router::dispatch();
