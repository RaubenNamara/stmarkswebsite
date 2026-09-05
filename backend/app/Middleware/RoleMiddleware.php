<?php

declare(strict_types=1);

namespace StMarks\Backend\Middleware;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Support\Middleware;

/**
 * Built for parity with eSpace's role: DSL but not attached to any route yet - stmarkswebsite
 * keeps a single-tier admin model for now (see the plan's Auth model decision), so there is
 * nothing to check roles against. Kept ready in case real roles are introduced later.
 */
class RoleMiddleware extends Middleware
{
    public function __construct(?object $context, private array $allowedRoles = [])
    {
        parent::__construct($context);
    }

    public function handle(): bool
    {
        /** @var Controller $controller */
        $controller = $this->context;

        if (!$controller->isAuthenticated()) {
            $controller->unauthorized();
            return false;
        }

        $role = $_SESSION['role'] ?? null;
        if ($this->allowedRoles && !in_array($role, $this->allowedRoles, true)) {
            $controller->forbidden();
            return false;
        }

        return true;
    }
}
