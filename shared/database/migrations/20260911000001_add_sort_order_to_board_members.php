<?php

declare(strict_types=1);

/** Lets admins manually reorder board members instead of always sorting by creation date. */
return new class {
    public function up(PDO $db): void
    {
        $db->exec('ALTER TABLE `board_members` ADD COLUMN `sort_order` int(11) NOT NULL DEFAULT 0 AFTER `photo`');
        $db->exec('UPDATE `board_members` SET `sort_order` = `id`');
    }

    public function down(PDO $db): void
    {
        $db->exec('ALTER TABLE `board_members` DROP COLUMN `sort_order`');
    }
};
