<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\User;

/**
 * Login/logout for the single-tier admin `users` table (any row is a full admin - see the plan's
 * Auth model decision). Existing password hashes are Laravel bcrypt, verified as-is.
 */
class AuthService extends Service
{
    public function __construct(private User $userModel = new User())
    {
    }

    /**
     * @return array{ok: bool, user?: array, message?: string}
     */
    public function login(string $email, string $password): array
    {
        $errors = $this->validate(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if ($errors) {
            return ['ok' => false, 'message' => reset($errors)];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !$this->verifyPassword($password, $user['password'])) {
            return ['ok' => false, 'message' => 'Invalid email or password'];
        }

        $_SESSION['user_id'] = (int) $user['id'];
        session_regenerate_id(true);
        // session_regenerate_id() issues a fresh session id/cookie but keeps $_SESSION contents,
        // so user_id set above survives - re-asserting it here only guards against a stale read.
        $_SESSION['user_id'] = (int) $user['id'];

        return ['ok' => true, 'user' => $this->userModel->hideFields($user)];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function currentUser(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        return $this->userModel->find((int) $userId);
    }
}
