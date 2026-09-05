<?php

declare(strict_types=1);

namespace StMarks\Backend\Middleware;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Support\Middleware;

/**
 * Validates X-CSRF-Token against the session token for mutating requests. Unlike eSpace (where
 * this class exists but is never attached to any route), this IS wired to every mutating /admin
 * route (see routes/api.php) - the admin SPA is same-origin so the session cookie rides along on
 * every request regardless of origin, making CSRF protection meaningful here.
 */
class CSRFMiddleware extends Middleware
{
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function handle(): bool
    {
        /** @var Controller $controller */
        $controller = $this->context;

        // Only the header is checked (not a body field) - the request body was already consumed
        // by Controller::parseRequest() before this middleware runs, and php://input can't be
        // reliably re-read. The admin SPA's api client always sends the token as a header.
        $headers = getallheaders() ?: [];
        $provided = $headers['X-CSRF-Token'] ?? $headers['X-Csrf-Token'] ?? null;

        $expected = $_SESSION['csrf_token'] ?? null;

        if (!$expected || !$provided || !hash_equals($expected, (string) $provided)) {
            // 403, not Laravel's conventional 419 - this Apache/mod_php setup silently rewrites
            // that (and other status codes outside its known-status table) to a bare 500, which
            // would make CSRF failures indistinguishable from real server errors to the client.
            $controller->error('CSRF token mismatch', 403);
            return false;
        }

        return true;
    }
}
