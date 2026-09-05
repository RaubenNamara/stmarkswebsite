<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

/**
 * Triggers a rebuild of public-frontend/ (the prerendered Vue site) whenever admin content that
 * appears on the public site changes - otherwise the static HTML would only ever reflect
 * whatever existed at the last manual build. Fire-and-forget: the admin request that changed the
 * content returns immediately, the actual `npm run build` (a few dozen static pages) runs
 * detached in the background. No queue/locking - rebuilds are fast and idempotent, so two rapid
 * edits occasionally overlapping is an accepted simplification, not a bug.
 */
class PublicSiteBuildService
{
    public static function trigger(): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $frontendDir = $projectRoot . '/public-frontend';
        $logDir = $projectRoot . '/storage/logs';
        $logFile = $logDir . '/public-site-build.log';

        if (!is_dir($frontendDir)) {
            return;
        }
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $cmd = 'cmd /C "cd /d "' . $frontendDir . '" && npm run build >> "' . $logFile . '" 2>&1"';

        if (stripos(PHP_OS, 'WIN') === 0) {
            pclose(popen('start /B "" ' . $cmd, 'r'));
        } else {
            exec($cmd . ' > /dev/null 2>&1 &');
        }

        Logger::info('Public site rebuild triggered');
    }
}
