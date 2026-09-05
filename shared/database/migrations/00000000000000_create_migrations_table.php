<?php

declare(strict_types=1);

/**
 * The migration-tracking table itself. In practice scripts/migrate.php also creates this table
 * with CREATE TABLE IF NOT EXISTS before it reads the migrations directory (chicken-and-egg: it
 * needs to exist before any migration, including this one, can be recorded as applied) - this
 * file exists for documentation/fresh-environment parity, not because the runner depends on it
 * being "run" in the normal sense.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(
            'CREATE TABLE IF NOT EXISTS `migrations` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) NOT NULL,
                `batch` int NOT NULL,
                `run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migrations_migration_unique` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS `migrations`');
    }
};
