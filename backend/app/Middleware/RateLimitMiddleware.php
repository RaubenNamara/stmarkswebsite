<?php

declare(strict_types=1);

namespace StMarks\Backend\Middleware;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Middleware;

/**
 * Simple file-based sliding-window rate limiter, keyed by client IP + a caller-supplied bucket
 * name (so /auth/login and each public form endpoint get independent limits). No Redis dependency
 * (eSpace's Redis-with-session-fallback is more infrastructure than this app needs) - a flock'd
 * JSON file per (bucket, ip) is enough for these low-traffic endpoints.
 */
class RateLimitMiddleware extends Middleware
{
    public function __construct(
        ?object $context,
        private string $bucket = 'default',
        private int $maxRequests = 10,
        private int $windowSeconds = 60
    ) {
        parent::__construct($context);
    }

    public function handle(): bool
    {
        /** @var Controller $controller */
        $controller = $this->context;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $dir = Config::getStoragePath('cache/ratelimit');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . DIRECTORY_SEPARATOR . md5($this->bucket . ':' . $ip) . '.json';

        $handle = fopen($file, 'c+');
        if ($handle === false) {
            return true; // fail open rather than blocking legitimate traffic on a filesystem hiccup
        }

        flock($handle, LOCK_EX);
        $raw = stream_get_contents($handle);
        $state = $raw ? json_decode($raw, true) : null;
        $now = time();

        if (!is_array($state) || ($now - ($state['window_start'] ?? 0)) >= $this->windowSeconds) {
            $state = ['window_start' => $now, 'count' => 0];
        }

        $state['count']++;
        $exceeded = $state['count'] > $this->maxRequests;

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state));
        flock($handle, LOCK_UN);
        fclose($handle);

        if ($exceeded) {
            $retryAfter = $this->windowSeconds - ($now - $state['window_start']);
            header('Retry-After: ' . max($retryAfter, 1));
            $controller->error('Too many requests, please try again later.', 429);
            return false;
        }

        return true;
    }
}
