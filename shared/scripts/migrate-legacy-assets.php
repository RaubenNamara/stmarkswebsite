<?php

declare(strict_types=1);

/**
 * One-off migration: copies every file referenced by a legacy (pre-rewrite) Laravel
 * Storage::url()-style path - no leading slash, e.g. "news/xxx.jpg" - from
 * storage/app/public/<path> into storage/uploads/<path>, then rewrites the owning DB row's
 * column to the new-stack convention "/uploads/<path>". Once every row is migrated,
 * Assets::resolve()'s LEGACY_STORAGE_URL branch (which depends on the old Laravel app's
 * public/storage symlink staying up) can be deleted and Laravel's files removed entirely.
 *
 * The (table, column) list below is every column ever passed through Assets::resolve() -
 * confirmed by grepping every call site in shared/src/Services.
 *
 * Usage:
 *   php scripts/migrate-legacy-assets.php            (dry run - reports only, writes nothing)
 *   php scripts/migrate-legacy-assets.php --apply    (copies files and updates the DB)
 */

require __DIR__ . '/../vendor/autoload.php';

use StMarks\Shared\Config\Config;
use StMarks\Shared\Config\Database;

Config::load();

$apply = in_array('--apply', $argv, true);

$targets = [
    ['news', 'image_path'],
    ['slides', 'image_path'],
    ['slides', 'video_path'],
    ['gallery_images', 'image_path'],
    ['club_images', 'image_path'],
    ['media', 'file_path'],
    ['job_applications', 'file_path'],
    ['campus_voices', 'featured_image'],
    ['co_curriculars', 'image'],
    ['co_curriculars', 'video'],
    ['girl_boy_talks', 'image'],
    ['girl_boy_talks', 'video'],
    ['mentorships', 'image'],
    ['mentorships', 'video'],
    ['chaplaincies', 'image'],
    ['chaplaincies', 'video'],
    ['inspiration_nights', 'image'],
    ['inspiration_nights', 'video'],
    ['christmas_cantatas', 'image'],
    ['christmas_cantatas', 'video'],
    ['student_leaderships', 'image_path'],
    ['student_leaderships', 'video_path'],
    ['smosa_alumnis', 'photo'],
    ['smosa_alumnis', 'video'],
    ['board_members', 'photo'],
    ['high_achievers', 'photo'],
    ['staff', 'photo'],
    ['fee_structures', 'file_path'],
    ['performances', 'pdf'],
];

$db = Database::getInstance();

// Config::getStoragePath() resolves to shared/storage (used only for this app's own logs) -
// the legacy Laravel files physically live at the project root's storage/app/public instead.
$legacyBase = dirname(__DIR__, 2) . '/storage/app/public';

$copied = 0;
$missing = [];
$collisions = [];

foreach ($targets as [$table, $column]) {
    $stmt = $db->prepare(
        "SELECT id, `{$column}` AS value FROM `{$table}` " .
        "WHERE `{$column}` IS NOT NULL AND `{$column}` != '' " .
        "AND `{$column}` NOT LIKE '/%' AND `{$column}` NOT LIKE 'http%'"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $value = $row['value'];
        $srcAbs = $legacyBase . '/' . $value;
        $dstAbs = Config::getUploadsPath($value);

        if (!is_file($srcAbs)) {
            $missing[] = "{$table}.{$column}#{$row['id']}: source missing at {$srcAbs}";
            continue;
        }

        if (is_file($dstAbs)) {
            $collisions[] = "{$table}.{$column}#{$row['id']}: destination already exists at {$dstAbs}";
            continue;
        }

        echo ($apply ? '[copy] ' : '[dry-run] ') . "{$table}.{$column}#{$row['id']}: {$value} -> /uploads/{$value}\n";

        if ($apply) {
            $dstDir = dirname($dstAbs);
            if (!is_dir($dstDir) && !mkdir($dstDir, 0755, true) && !is_dir($dstDir)) {
                $missing[] = "{$table}.{$column}#{$row['id']}: could not create directory {$dstDir}";
                continue;
            }

            if (!copy($srcAbs, $dstAbs)) {
                $missing[] = "{$table}.{$column}#{$row['id']}: copy failed";
                continue;
            }

            $update = $db->prepare("UPDATE `{$table}` SET `{$column}` = :new WHERE id = :id");
            $update->execute(['new' => '/uploads/' . $value, 'id' => $row['id']]);
        }

        $copied++;
    }
}

echo "\n" . ($apply ? 'Applied' : 'Planned') . ": {$copied} file(s)\n";

if ($missing) {
    echo "\nMissing sources / write failures (" . count($missing) . "):\n";
    foreach ($missing as $line) {
        echo "  - {$line}\n";
    }
}

if ($collisions) {
    echo "\nCollisions - destination already exists, skipped, review manually (" . count($collisions) . "):\n";
    foreach ($collisions as $line) {
        echo "  - {$line}\n";
    }
}

if (!$apply) {
    echo "\nThis was a dry run. Re-run with --apply to actually copy files and update the database.\n";
}
