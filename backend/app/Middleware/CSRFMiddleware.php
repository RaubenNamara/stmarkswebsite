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

        // Read from $_SERVER rather than getallheaders()/apache_request_headers(): PHP always
        // normalizes $_SERVER's HTTP_* keys the same way regardless of the header's original
        // casing, but getallheaders() returns whatever casing the request actually arrived with -
        // a Node-based proxy (e.g. the admin-frontend dev server's Vite proxy) lowercases every
        // header it forwards, so an exact-case lookup like $headers['X-CSRF-Token'] silently
        // misses it there even though the value did arrive, failing every request behind one.
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

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
