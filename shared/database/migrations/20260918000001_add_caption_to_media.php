<?php

declare(strict_types=1);

/** Lets admins attach a short text caption to each media item, shown under its title in the homepage slideshow. */
return new class {
    public function up(PDO $db): void
    {
        $db->exec('ALTER TABLE `media` ADD COLUMN `caption` text DEFAULT NULL AFTER `title`');
    }

    public function down(PDO $db): void
    {
        $db->exec('ALTER TABLE `media` DROP COLUMN `caption`');
    }
};
