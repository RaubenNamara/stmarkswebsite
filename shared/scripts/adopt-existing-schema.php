<?php

declare(strict_types=1);

/**
 * One-time script for the live DB only: marks every baseline migration as already-applied in the
 * `migrations` tracking table WITHOUT running its up() - because the live stmarkswebsite database
 * already has these tables (created by Laravel's migrations), and running CREATE TABLE against it
 * would fail/conflict. A fresh environment (new dev machine, CI, staging) should use
 * `php scripts/migrate.php migrate` instead, which runs the real up() methods.
 *
 * Safe to re-run: skips any migration already marked applied. Refuses to touch a table that
 * doesn't actually exist yet (that should go through the real migrate.php instead).
 *
 * Usage: php scripts/adopt-existing-schema.php
 */

require __DIR__ . '/../vendor/autoload.php';

use StMarks\Shared\Config\Config;
use StMarks\Shared\Config\Database;

Config::load();
$db = Database::getInstance();

$db->exec(
    'CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `batch` int NOT NULL,
        `run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `migrations_migration_unique` (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$stmt = $db->query('SELECT migration FROM migrations');
$applied = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));

$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*_create_*_table.php');
sort($files);

$batch = 0; // adoption batch, distinct from any real migrate run
$marked = 0;
$skippedMissingTable = [];

foreach ($files as $file) {
    $name = basename($file, '.php');
    if ($name === '00000000000000_create_migrations_table' || isset($applied[$name])) {
        continue;
    }

    if (!preg_match('/create_(\w+)_table$/', $name, $m)) {
        continue;
    }
    $table = $m[1];

    $exists = $db->query("SHOW TABLES LIKE " . $db->quote($table))->fetchColumn();
    if (!$exists) {
        $skippedMissingTable[] = $table;
        continue;
    }

    $stmt = $db->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
    $stmt->execute(['migration' => $name, 'batch' => $batch]);
    echo "Adopted: {$name} (table `{$table}` already existed, not recreated)\n";
    $marked++;
}

echo "\nDone - {$marked} migration(s) marked as already-applied.\n";
if ($skippedMissingTable) {
    echo "Skipped (table not found, run migrate.php for these instead): " . implode(', ', $skippedMissingTable) . "\n";
}
