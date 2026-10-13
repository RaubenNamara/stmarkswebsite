<?php

declare(strict_types=1);

/**
 * Chatbot analytics: who talks to the website assistant and what they ask.
 *
 * Privacy by design: visitors are identified only by a random id generated in their own browser (no
 * name, email or raw IP address is stored - `ip_hash` is a salted, monthly-rotating hash used only to spot
 * abuse), and the message text is redacted server-side (emails, phone-like numbers, passwords) before saving.
 *
 *  - chatbot_conversations: one row per conversation (a visitor can have several).
 *  - chatbot_messages:      one row per question the visitor asked, with how the bot answered.
 */
return new class {
    public function up(PDO $db): void
    {
        $db->exec(
            'CREATE TABLE `chatbot_conversations` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `conversation_uid` varchar(32) NOT NULL,
                `visitor_uid` varchar(32) NOT NULL,
                `device` varchar(10) NOT NULL DEFAULT \'desktop\',
                `page_path` varchar(150) DEFAULT NULL,
                `ip_hash` varchar(16) DEFAULT NULL,
                `started_date` date NOT NULL,
                `message_count` int(10) unsigned NOT NULL DEFAULT 0,
                `unanswered_count` int(10) unsigned NOT NULL DEFAULT 0,
                `last_message_at` datetime DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `chatbot_conversations_uid_unique` (`conversation_uid`),
                KEY `chatbot_conversations_visitor_index` (`visitor_uid`),
                KEY `chatbot_conversations_started_date_index` (`started_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $db->exec(
            'CREATE TABLE `chatbot_messages` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `conversation_id` bigint(20) unsigned NOT NULL,
                `question` varchar(300) NOT NULL,
                `answer_kind` varchar(16) NOT NULL,
                `answer_ref` varchar(40) DEFAULT NULL,
                `answered` tinyint(1) NOT NULL DEFAULT 1,
                `spelling_fixed` tinyint(1) NOT NULL DEFAULT 0,
                `answer_preview` varchar(300) DEFAULT NULL,
                `message_date` date NOT NULL,
                `message_hour` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `chatbot_messages_conversation_index` (`conversation_id`),
                KEY `chatbot_messages_date_index` (`message_date`),
                KEY `chatbot_messages_answered_date_index` (`answered`, `message_date`),
                CONSTRAINT `chatbot_messages_conversation_fk` FOREIGN KEY (`conversation_id`)
                    REFERENCES `chatbot_conversations` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS `chatbot_messages`');
        $db->exec('DROP TABLE IF EXISTS `chatbot_conversations`');
    }
};
