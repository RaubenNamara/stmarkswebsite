<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

/**
 * Hand-rolled router shared by backend/ (JSON API) and public-site/ (HTML pages) - same
 * registration DSL and matching logic as eSpace's Router.php, but with pluggable not-found/error
 * handlers and middleware resolution so each app can render its own response shape (JSON vs HTML)
 * instead of the JSON-only behaviour baked into eSpace's copy. Also catches \Throwable (not just
 * \Exception, eSpace's gap) and logs via Logger instead of scattering error_log() calls.
 */
class Router
{
    /** @var array<string, array<string, array{handler: array|string|callable, middleware: array}>> */
    private static array $routes = [];
    private static array $groupMiddleware = [];
    private static string $currentGroupPrefix = '';

    /** Path segment the request URI is anchored on when SCRIPT_NAME-based stripping doesn't match (e.g. '/api/' for backend). Null = no anchor, just base-path stripping. */
    private static ?string $uriAnchor = null;

    /** Extra literal prefix to also try stripping when no anchor is set (public-site's case - see getRequestUriCandidates()). */
    private static ?string $extraBasePathPrefix = null;

    /** @var (callable(string $method, string $uri): void)|null */
    private static $notFoundHandler = null;

    /** @var (callable(\Throwable $e): void)|null */
    private static $errorHandler = null;

    /** @var (callable(string $name, ?object $context): ?Middleware)|null */
    private static $middlewareResolver = null;

    public static function get(string $path, array|string|callable $handler, array $middleware = []): void
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, array|string|callable $handler, array $middleware = []): void
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put(string $path, array|string|callable $handler, array $middleware = []): void
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function patch(string $path, array|string|callable $handler, array $middleware = []): void
    {
        self::addRoute('PATCH', $path, $handler, $middleware);
    }

    public static function delete(string $path, array|string|callable $handler, array $middleware = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    private static function addRoute(string $method, string $path, array|string|callable $handler, array $middleware): void
    {
        $fullPath = self::$currentGroupPrefix . $path;

        self::$routes[$method][$fullPath] = [
            'handler' => $handler,
            'middleware' => array_merge(self::$groupMiddleware, $middleware),
        ];
    }

    public static function group(array $attributes, callable $callback): void
    {
        $previousPrefix = self::$currentGroupPrefix;
        $previousMiddleware = self::$groupMiddleware;

        if (isset($attributes['prefix'])) {
            self::$currentGroupPrefix .= $attributes['prefix'];
        }

        if (isset($attributes['middleware'])) {
            self::$groupMiddleware = array_merge(self::$groupMiddleware, (array) $attributes['middleware']);
        }

        $callback();

        self::$currentGroupPrefix = $previousPrefix;
        self::$groupMiddleware = $previousMiddleware;
    }

    public static function setUriAnchor(?string $anchor): void
    {
        self::$uriAnchor = $anchor;
    }

    /**
     * Only meaningful when no anchor is set. Public-site has no fixed prefix to anchor on (its
     * routes are the app's own arbitrary URL space, e.g. '/news', '/staff'), so unlike backend it
     * can't rely purely on searching the visible URL text. This adds one more literal prefix
     * dispatch() will try stripping, alongside its existing SCRIPT_NAME-derived guess - lets both
     * a direct hit (.../public-site/public/news) and a root-unified one (.../stmarkswebsite/news,
     * reached via a project-root .htaccess rewrite where SCRIPT_NAME no longer reflects the
     * public-facing path) resolve correctly at the same time.
     */
    public static function setExtraBasePathPrefix(?string $prefix): void
    {
        self::$extraBasePathPrefix = $prefix;
    }

    /** @param callable(string $method, string $uri): void $handler */
    public static function setNotFoundHandler(callable $handler): void
    {
        self::$notFoundHandler = $handler;
    }

    /** @param callable(\Throwable $e): void $handler */
    public static function setErrorHandler(callable $handler): void
    {
        self::$errorHandler = $handler;
    }

    /** @param callable(string $name, ?object $context): ?Middleware $resolver */
    public static function setMiddlewareResolver(callable $resolver): void
    {
        self::$middlewareResolver = $resolver;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $candidates = self::getRequestUriCandidates();

        foreach ($candidates as $uri) {
            $route = self::findRoute($method, $uri);
            if ($route) {
                $params = self::extractParams($uri, $route['pattern']);
                self::executeHandler($route['handler'], $params, $route['middleware'], $route['pattern']);
                return;
            }
        }

        self::notFound($method, $candidates[0] ?? '/');
    }

    /**
     * Returns one or more candidate route paths to try, in order, for the current request.
     * Anchor mode (backend) always yields exactly one - it's unambiguous by construction. No-
     * anchor mode (public-site) yields every stripping strategy that could plausibly be correct,
     * since which one actually is correct depends on how this specific request physically reached
     * the script (see setExtraBasePathPrefix()'s docblock).
     *
     * @return string[]
     */
    private static function getRequestUriCandidates(): array
    {
        $raw = $_SERVER['REQUEST_URI'] ?? '/';
        if (($pos = strpos($raw, '?')) !== false) {
            $raw = substr($raw, 0, $pos);
        }

        if (self::$uriAnchor !== null) {
            $anchorPos = strpos($raw, self::$uriAnchor);
            $uri = $anchorPos !== false ? substr($raw, $anchorPos) : $raw;
            return ['/' . trim($uri, '/')];
        }

        $candidates = [];

        $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($basePath && $basePath !== '/' && str_starts_with($raw, $basePath)) {
            $candidates[] = '/' . trim(substr($raw, strlen($basePath)), '/');
        }

        if (self::$extraBasePathPrefix && str_starts_with($raw, self::$extraBasePathPrefix)) {
            $candidates[] = '/' . trim(substr($raw, strlen(self::$extraBasePathPrefix)), '/');
        }

        $candidates[] = '/' . trim($raw, '/');

        return array_values(array_unique($candidates));
    }

    private static function findRoute(string $method, string $uri): ?array
    {
        if (!isset(self::$routes[$method])) {
            return null;
        }

        foreach (self::$routes[$method] as $pattern => $route) {
            if (preg_match(self::convertToRegex($pattern), $uri)) {
                return [
                    'handler' => $route['handler'],
                    'middleware' => $route['middleware'],
                    'pattern' => $pattern,
                ];
            }
        }

        return null;
    }

    private static function convertToRegex(string $pattern): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $pattern);
        $pattern = str_replace('/', '\/', $pattern);

        return '/^' . $pattern . '$/';
    }

    private static function extractParams(string $uri, string $pattern): array
    {
        if (preg_match(self::convertToRegex($pattern), $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return [];
    }

    private static function executeHandler(array|string|callable $handler, array $params, array $middleware, string $pattern): void
    {
        try {
            if (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $method] = explode('@', $handler);
                $handler = [$controllerClass, $method];
            }

            $controller = null;
            $method = null;

            if (is_array($handler) && is_string($handler[0])) {
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass();
            } elseif (is_array($handler) && is_object($handler[0])) {
                [$controller, $method] = $handler;
            }

            foreach ($middleware as $mw) {
                $instance = self::instantiateMiddleware($mw, $controller);
                if ($instance !== null && !$instance->handle()) {
                    return;
                }
            }

            global $routeParams;
            $routeParams = [];
            preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $pattern, $paramNames);
            foreach ($paramNames[1] as $index => $paramName) {
                if (isset($params[$index])) {
                    $routeParams[$paramName] = $params[$index];
                }
            }

            if ($controller !== null && $method !== null) {
                if (!method_exists($controller, $method)) {
                    throw new \RuntimeException("Route handler method does not exist: {$method}");
                }
                $controller->$method(...$params);
            } elseif (is_callable($handler)) {
                call_user_func_array($handler, $params);
            }
        } catch (\Throwable $e) {
            self::handleError($e);
        }
    }

    private static function instantiateMiddleware(string $middleware, ?object $context): ?Middleware
    {
        if (self::$middlewareResolver === null) {
            return null;
        }

        return (self::$middlewareResolver)($middleware, $context);
    }

    private static function notFound(string $method, string $uri): void
    {
        if (self::$notFoundHandler !== null) {
            (self::$notFoundHandler)($method, $uri);
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Route not found']);
        exit;
    }

    private static function handleError(\Throwable $e): void
    {
        if (self::$errorHandler !== null) {
            (self::$errorHandler)($e);
            return;
        }

        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
        exit;
    }
}
