<?php

declare(strict_types=1);

/**
 * Baseline migration for the `news` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `news` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `excerpt` text DEFAULT NULL,
            `content` longtext NOT NULL,
            `image_path` varchar(255) DEFAULT NULL,
            `is_published` tinyint(1) NOT NULL DEFAULT 1,
            `published_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `news_slug_unique` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `news`");
    }
};
