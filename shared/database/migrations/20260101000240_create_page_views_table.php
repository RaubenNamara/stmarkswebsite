<?php

declare(strict_types=1);

/**
 * Baseline migration for the `page_views` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `page_views` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `page_url` varchar(255) NOT NULL,
            `page_type` varchar(255) DEFAULT NULL,
            `page_id` bigint(20) unsigned DEFAULT NULL,
            `ip_address` varchar(255) DEFAULT NULL,
            `user_agent` varchar(255) DEFAULT NULL,
            `view_date` date NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `page_views_page_type_page_id_view_date_index` (`page_type`,`page_id`,`view_date`),
            KEY `page_views_view_date_index` (`view_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `page_views`");
    }
};
