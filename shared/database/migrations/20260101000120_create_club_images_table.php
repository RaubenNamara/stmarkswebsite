<?php

declare(strict_types=1);

/**
 * Baseline migration for the `club_images` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `club_images` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `club_id` bigint(20) unsigned NOT NULL,
            `image_path` varchar(255) NOT NULL,
            `caption` varchar(255) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `club_images_club_id_foreign` (`club_id`),
            CONSTRAINT `club_images_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `club_images`");
    }
};
