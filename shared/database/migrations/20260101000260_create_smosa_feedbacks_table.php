<?php

declare(strict_types=1);

/**
 * Baseline migration for the `smosa_feedbacks` table - schema captured verbatim (SHOW CREATE TABLE)
 * from the live stmarkswebsite database on 2026-09-04, not replayed from Laravel's incremental
 * migration history. On the live DB this is marked applied by scripts/adopt-existing-schema.php
 * without running up() (the table already exists); a fresh environment runs up() for real.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE `smosa_feedbacks` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `overall_experience` varchar(255) NOT NULL,
            `rating_event_organization` varchar(255) NOT NULL,
            `rating_communication` varchar(255) NOT NULL,
            `rating_venue_setup` varchar(255) NOT NULL,
            `rating_programme_activities` varchar(255) NOT NULL,
            `rating_food_refreshments` varchar(255) NOT NULL,
            `rating_entertainment` varchar(255) NOT NULL,
            `rating_guest_experience` varchar(255) NOT NULL,
            `rating_time_management` varchar(255) NOT NULL,
            `rating_photography_video` varchar(255) DEFAULT NULL,
            `best_part` text DEFAULT NULL,
            `improvements` text DEFAULT NULL,
            `future_suggestions` text DEFAULT NULL,
            `other_comments` text DEFAULT NULL,
            `future_participation` varchar(255) NOT NULL,
            `activities_interest` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activities_interest`)),
            `activities_other` varchar(255) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS `smosa_feedbacks`");
    }
};
