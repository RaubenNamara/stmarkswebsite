<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Support\Validator;

/**
 * Base class for business-logic services. Controllers stay thin (request/response only);
 * anything domain-specific (validation rule sets, cross-model logic, side effects) belongs here.
 */
abstract class Service
{
    protected function validate(array $data, array $rules): array
    {
        return Validator::validate($data, $rules);
    }

    protected function sanitize(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    protected function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    protected function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
