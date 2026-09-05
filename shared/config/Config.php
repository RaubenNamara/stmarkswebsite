<?php

declare(strict_types=1);

namespace StMarks\Shared\Config;

use PDO;

/**
 * Centralized configuration: loads shared/.env once and exposes typed getters.
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(__DIR__) . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    self::$config[trim($key)] = trim($value);
                }
            }
        }

        self::$config = array_merge([
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_NAME' => 'stmarkswebsite',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_NAME' => "St Mark's College Namagoma",
            'ADMIN_FRONTEND_URL' => 'http://localhost/stmarkswebsite/admin',
            // Old Laravel app's storage symlink - see Support\Assets for why this exists.
            'LEGACY_STORAGE_URL' => 'http://localhost/stmarkswebsite/public/storage',
            // See public-site/public/index.php's comment - empty in production.
            'PUBLIC_SITE_BASE_PATH' => '',
            'SESSION_LIFETIME' => '7200',
            'CSRF_TOKEN_NAME' => 'csrf_token',
            'STORAGE_PATH' => dirname(__DIR__) . '/storage',
            'UPLOADS_PATH' => dirname(dirname(__DIR__)) . '/storage/uploads',
            'MAX_UPLOAD_SIZE' => '52428800',
            'MAIL_HOST' => '',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => '',
            'MAIL_PASSWORD' => '',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => '',
            'MAIL_FROM_NAME' => "St Mark's College Namagoma",
            'CONTACT_RECIPIENTS' => '',
        ], self::$config);

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        return self::$config[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::load();
        self::$config[$key] = $value;
    }

    public static function isDebug(): bool
    {
        return self::get('APP_DEBUG') === 'true';
    }

    public static function getDatabaseConfig(): array
    {
        self::load();

        return [
            'host' => self::get('DB_HOST'),
            'port' => (int) self::get('DB_PORT'),
            'database' => self::get('DB_NAME'),
            'username' => self::get('DB_USER'),
            'password' => self::get('DB_PASS'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ];
    }

    public static function getMailConfig(): array
    {
        self::load();

        return [
            'host' => self::get('MAIL_HOST'),
            'port' => (int) self::get('MAIL_PORT'),
            'username' => self::get('MAIL_USERNAME'),
            'password' => self::get('MAIL_PASSWORD'),
            'encryption' => self::get('MAIL_ENCRYPTION'),
            'from_address' => self::get('MAIL_FROM_ADDRESS'),
            'from_name' => self::get('MAIL_FROM_NAME'),
        ];
    }

    public static function getStoragePath(string $path = ''): string
    {
        $basePath = self::get('STORAGE_PATH');
        return $basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public static function getUploadsPath(string $path = ''): string
    {
        $basePath = self::get('UPLOADS_PATH');
        return $basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public static function getMaxUploadSize(): int
    {
        return (int) self::get('MAX_UPLOAD_SIZE');
    }
}
