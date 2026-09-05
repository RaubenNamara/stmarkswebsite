<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

/**
 * Single-tier admin users table (see the plan's Auth model decision: any row here is a full
 * admin - no role column). Password hashes are Laravel bcrypt, verified as-is by
 * password_verify() - no rehashing needed.
 */
class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'password', 'email_verified_at', 'remember_token'];
    protected array $hidden = ['password', 'remember_token'];
    protected array $searchable = ['name', 'email'];

    public function findByEmail(string $email): ?array
    {
        return $this->query('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email])->fetch() ?: null;
    }
}
