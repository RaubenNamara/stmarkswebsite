<?php

declare(strict_types=1);

/**
 * Creates an admin account (the `users` table is single-tier - any row in it is a full admin,
 * see the plan's Auth model decision). Replaces `php artisan tinker` from the Laravel app, since
 * there is no self-serve registration.
 *
 * Usage: php scripts/create-admin.php "Full Name" "email@example.com" "password"
 */

require __DIR__ . '/../vendor/autoload.php';

use StMarks\Shared\Config\Config;
use StMarks\Shared\Models\User;

Config::load();

[, $name, $email, $password] = $argv + [null, null, null, null];

if (!$name || !$email || !$password) {
    fwrite(STDERR, "Usage: php scripts/create-admin.php \"Full Name\" \"email@example.com\" \"password\"\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

$userModel = new User();

if ($userModel->findByEmail($email)) {
    fwrite(STDERR, "A user with that email already exists.\n");
    exit(1);
}

$id = $userModel->create([
    'name' => $name,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_BCRYPT),
]);

if ($id === false) {
    fwrite(STDERR, "Failed to create user.\n");
    exit(1);
}

echo "Admin user created: {$email} (id {$id})\n";
