<?php

declare(strict_types=1);

namespace StMarks\Backend\Support;

use StMarks\Backend\Middleware\AuthMiddleware;
use StMarks\Backend\Middleware\CSRFMiddleware;
use StMarks\Backend\Middleware\RateLimitMiddleware;
use StMarks\Backend\Middleware\RoleMiddleware;
use StMarks\Shared\Support\Middleware;

/**
 * Resolves the string middleware names used in routes/api.php into instances, wired into
 * Router::setMiddlewareResolver(). DSL: 'auth', 'csrf', 'role:admin,editor',
 * 'rate_limit:<bucket>,<maxRequests>,<windowSeconds>' (all three rate_limit params optional).
 */
class MiddlewareRegistry
{
    public static function resolve(string $name, ?object $context): ?Middleware
    {
        if ($name === 'auth') {
            return new AuthMiddleware($context);
        }

        if ($name === 'csrf') {
            return new CSRFMiddleware($context);
        }

        if (str_starts_with($name, 'role:')) {
            $roles = explode(',', substr($name, 5));
            return new RoleMiddleware($context, $roles);
        }

        if (str_starts_with($name, 'rate_limit')) {
            $params = str_contains($name, ':') ? explode(',', substr($name, strpos($name, ':') + 1)) : [];
            return new RateLimitMiddleware(
                $context,
                $params[0] ?? 'default',
                isset($params[1]) ? (int) $params[1] : 10,
                isset($params[2]) ? (int) $params[2] : 60
            );
        }

        return null;
    }
}
