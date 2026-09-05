<?php

declare(strict_types=1);

/**
 * Baseline migration for the `slides` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `slides` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `image_path` varchar(255) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `caption` varchar(255) DEFAULT NULL,
            `order` int(11) NOT NULL DEFAULT 0,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `type` varchar(255) NOT NULL DEFAULT 'image',
            `video_path` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `slides`");
    }
};
