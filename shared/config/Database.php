<?php

declare(strict_types=1);

namespace StMarks\Shared\Config;

use PDO;
use PDOException;

/**
 * Singleton PDO connection manager with health-check auto-reconnect
 * (mirrors eSpace's Database.php, including the lastInsertId() note below).
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        try {
            self::$instance->query('SELECT 1');
        } catch (PDOException $e) {
            self::$instance = null;
            self::connect();
        }

        return self::$instance;
    }

    private static function connect(): void
    {
        self::$config = Config::getDatabaseConfig();

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            self::$config['host'],
            self::$config['port'],
            self::$config['database'],
            self::$config['charset']
        );

        try {
            self::$instance = new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                self::$config['options']
            );

            self::$instance->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $e) {
            if (Config::isDebug()) {
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
            throw new PDOException('Database connection failed');
        }
    }

    public static function close(): void
    {
        self::$instance = null;
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    /**
     * Deliberately does NOT go through getInstance() - its health-check `SELECT 1` resets PDO's
     * tracked last-insert-id for PDO_MySQL. The instance is always already connected here since
     * an insert was just run through it.
     */
    public static function lastInsertId(): string|false
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance->lastInsertId();
    }
}
