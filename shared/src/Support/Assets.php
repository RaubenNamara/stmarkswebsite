<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

use StMarks\Shared\Config\Config;

/**
 * Resolves a path stored on a model to a browsable URL. Used by both backend/ (JSON API, read by
 * the admin SPA on a different origin/dev-server) and public-site/ (server-rendered HTML) - one
 * resolution rule instead of two, so admin and public always agree on where an asset lives.
 *
 * Two conventions exist in the live data during the parallel-build period:
 * - New rows written by the new stack already start with "/uploads/..." (UploadService's
 *   convention) - a root-relative path, correct as-is only when the app is deployed at the
 *   domain root (production's plan). Locally everything lives under a /stmarkswebsite/
 *   subfolder, so it's prefixed with PUBLIC_SITE_BASE_PATH (empty in production) - the browser
 *   resolves a root-relative src against the *domain*, not the current page's path, so without
 *   this every uploaded image/PDF would 404 for any page not literally served from the domain
 *   root. A project-root `uploads` junction to storage/uploads (alongside the /admin and /api
 *   junctions) is what actually answers that prefixed URL locally.
 * - Old rows carry Laravel's Storage-facade-relative paths (e.g. "news/xxx.jpg", "slides/videos/
 *   xxx.mp4" - no leading slash), which only exist under the old Laravel app's storage. Rather
 *   than copy/migrate those files now, they're served via the old app's still-running
 *   /storage/... symlink (LEGACY_STORAGE_URL) for as long as it stays up during the parallel
 *   build - a real migration/copy step belongs in the cutover phase, not here.
 */
class Assets
{
    public static function resolve(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/uploads/')) {
            $prefix = rtrim((string) Config::get('PUBLIC_SITE_BASE_PATH', ''), '/');
            return $prefix . $path;
        }

        $legacyBase = rtrim(Config::get('LEGACY_STORAGE_URL', 'http://localhost/stmarkswebsite/public/storage'), '/');

        return $legacyBase . '/' . ltrim($path, '/');
    }
}
