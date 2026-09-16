<?php

declare(strict_types=1);

/**
 * Records suspicious/abusive requests (failed admin logins, rate-limit hits) so admins can audit
 * intrusion attempts - same shape/indexing approach as page_views (one row per event, a `date`
 * column indexed for the report's daily grouping).
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(
            'CREATE TABLE `security_events` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `event_type` varchar(50) NOT NULL,
                `ip_address` varchar(255) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `identifier` varchar(255) DEFAULT NULL,
                `request_path` varchar(255) DEFAULT NULL,
                `details` varchar(255) DEFAULT NULL,
                `event_date` date NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `security_events_event_date_index` (`event_date`),
                KEY `security_events_event_type_event_date_index` (`event_type`, `event_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS `security_events`');
    }
};
