<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

use StMarks\Shared\Config\Config;

/**
 * Resolves a path stored on a model to a browsable URL. Used by both backend/ (JSON API, read by
 * the admin SPA on a different origin/dev-server) and public-site/ (server-rendered HTML) - one
 * resolution rule instead of two, so admin and public always agree on where an asset lives.
 *
 * Every path is "/uploads/..." (UploadService's convention) - a root-relative path, correct as-is
 * only when the app is deployed at the domain root (production's plan). Locally everything lives
 * under a /stmarkswebsite/ subfolder, so it's prefixed with PUBLIC_SITE_BASE_PATH (empty in
 * production) - the browser resolves a root-relative src against the *domain*, not the current
 * page's path, so without this every uploaded image/PDF would 404 for any page not literally
 * served from the domain root. A project-root `uploads` junction to storage/uploads (alongside
 * the /admin and /api junctions) is what actually answers that prefixed URL locally.
 *
 * Laravel's old Storage-facade-relative paths (e.g. "news/xxx.jpg", no leading slash) were
 * migrated to this convention by shared/scripts/migrate-legacy-assets.php ahead of removing the
 * Laravel app entirely. A handful of rows had no file left to migrate (the physical file was
 * already gone before this rewrite) and still carry a bare, slash-less path - normalized below so
 * those at least produce a well-formed (still-404) URL instead of running into the previous
 * segment with no separator (e.g. "/stmarkswebsitestaff/..." instead of "/stmarkswebsite/staff/...").
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

        $prefix = rtrim((string) Config::get('PUBLIC_SITE_BASE_PATH', ''), '/');
        return $prefix . '/' . ltrim($path, '/');
    }
}
