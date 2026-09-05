<?php

declare(strict_types=1);

namespace StMarks\Backend\Middleware;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Support\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle(): bool
    {
        /** @var Controller|null $controller */
        $controller = $this->context;

        if ($controller === null) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if (!$controller->isAuthenticated()) {
            $controller->unauthorized();
            return false;
        }

        return true;
    }
}
