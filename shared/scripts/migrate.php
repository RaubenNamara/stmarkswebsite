<?php

declare(strict_types=1);

/**
 * Migration runner. Usage (from shared/):
 *   php scripts/migrate.php migrate           - run all pending migrations, ascending
 *   php scripts/migrate.php migrate:rollback   - roll back the most recent batch
 *   php scripts/migrate.php migrate:status     - list migrations and their applied state
 *
 * Unlike eSpace (which has no real migration system - untracked, non-idempotent ad-hoc scripts),
 * this tracks applied migrations in a `migrations` table with batch numbers, so rollback and
 * status are both meaningful.
 */

require __DIR__ . '/../vendor/autoload.php';

use StMarks\Shared\Config\Config;
use StMarks\Shared\Config\Database;

Config::load();

$migrationsDir = __DIR__ . '/../database/migrations';
$command = $argv[1] ?? 'migrate';

$db = Database::getInstance();

// Ensure the tracking table exists before anything else can be recorded.
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

function allMigrationFiles(string $dir): array
{
    $files = glob($dir . '/*.php');
    sort($files);
    return $files;
}

function appliedMigrations(PDO $db): array
{
    $stmt = $db->query('SELECT migration, batch FROM migrations ORDER BY id');
    $rows = $stmt->fetchAll();
    return array_combine(array_column($rows, 'migration'), array_column($rows, 'batch'));
}

function nameFor(string $file): string
{
    return basename($file, '.php');
}

switch ($command) {
    case 'migrate':
        $applied = appliedMigrations($db);
        $nextBatch = $applied ? max($applied) + 1 : 1;
        $ran = 0;

        foreach (allMigrationFiles($migrationsDir) as $file) {
            $name = nameFor($file);
            if ($name === '00000000000000_create_migrations_table') {
                continue; // handled above, always
            }
            if (isset($applied[$name])) {
                continue;
            }

            echo "Migrating: {$name}\n";
            $migration = require $file;
            $migration->up($db);

            $stmt = $db->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
            $stmt->execute(['migration' => $name, 'batch' => $nextBatch]);

            echo "Migrated:  {$name}\n";
            $ran++;
        }

        echo $ran > 0 ? "Done - {$ran} migration(s) applied.\n" : "Nothing to migrate.\n";
        break;

    case 'migrate:rollback':
        $applied = appliedMigrations($db);
        if (!$applied) {
            echo "Nothing to roll back.\n";
            break;
        }

        $lastBatch = max($applied);
        $toRollback = array_keys(array_filter($applied, fn ($batch) => $batch === $lastBatch));
        rsort($toRollback);

        foreach ($toRollback as $name) {
            $file = $migrationsDir . '/' . $name . '.php';
            if (!file_exists($file)) {
                echo "Skipping {$name} - migration file no longer exists.\n";
                continue;
            }

            echo "Rolling back: {$name}\n";
            $migration = require $file;
            $migration->down($db);

            $stmt = $db->prepare('DELETE FROM migrations WHERE migration = :migration');
            $stmt->execute(['migration' => $name]);

            echo "Rolled back:  {$name}\n";
        }
        break;

    case 'migrate:status':
        $applied = appliedMigrations($db);
        foreach (allMigrationFiles($migrationsDir) as $file) {
            $name = nameFor($file);
            if ($name === '00000000000000_create_migrations_table') {
                continue;
            }
            $status = isset($applied[$name]) ? "applied (batch {$applied[$name]})" : 'pending';
            echo sprintf("%-55s %s\n", $name, $status);
        }
        break;

    default:
        fwrite(STDERR, "Unknown command: {$command}\n");
        exit(1);
}
