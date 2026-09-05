<?php

declare(strict_types=1);

/**
 * Baseline migration for the `gallery_images` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `gallery_images` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `gallery_event_id` bigint(20) unsigned NOT NULL,
            `image_path` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `gallery_images_gallery_event_id_foreign` (`gallery_event_id`),
            CONSTRAINT `gallery_images_gallery_event_id_foreign` FOREIGN KEY (`gallery_event_id`) REFERENCES `gallery_events` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `gallery_images`");
    }
};
