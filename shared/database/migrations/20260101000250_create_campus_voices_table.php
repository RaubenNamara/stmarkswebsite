<?php

declare(strict_types=1);

/**
 * Baseline migration for the `campus_voices` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `campus_voices` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `student_name` varchar(255) NOT NULL,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `summary` text DEFAULT NULL,
            `author_bio` text DEFAULT NULL,
            `content` longtext NOT NULL,
            `featured_image` varchar(255) DEFAULT NULL,
            `category` varchar(255) DEFAULT NULL,
            `author` varchar(255) DEFAULT NULL,
            `featured` tinyint(1) NOT NULL DEFAULT 0,
            `status` varchar(255) NOT NULL DEFAULT 'draft',
            `views` int(10) unsigned NOT NULL DEFAULT 0,
            `published_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `campus_voices_slug_unique` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `campus_voices`");
    }
};
