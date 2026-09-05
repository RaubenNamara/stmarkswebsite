<?php

declare(strict_types=1);

// Global helper functions available to every template without a use statement (loaded via
// composer.json's "files" autoload, not PSR-4 - it has no class to autoload by name).

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset_url')) {
    /** Thin wrapper so templates don't need a use statement - see Shared\Support\Assets::resolve(). */
    function asset_url(?string $path): ?string
    {
        return \StMarks\Shared\Support\Assets::resolve($path);
    }
}
