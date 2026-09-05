<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

use StMarks\Shared\Config\Config;

/**
 * One structured logging entry point, replacing eSpace's scattered raw error_log() calls
 * (some of which logged sensitive data like password lengths). Never pass request bodies,
 * passwords, or tokens in $context.
 */
class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir = Config::getStoragePath('logs');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $line = sprintf(
            '[%s] %s: %s%s%s',
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '',
            PHP_EOL
        );

        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log', $line, FILE_APPEND);
    }
}
