<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers;

use StMarks\Backend\Middleware\CSRFMiddleware;
use StMarks\Shared\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        $result = $this->authService->login(
            (string) $this->input('email', ''),
            (string) $this->input('password', ''),
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        if (!$result['ok']) {
            $this->error($result['message'], 401);
            return;
        }

        $this->success([
            'user' => $result['user'],
            'csrf_token' => CSRFMiddleware::generateToken(),
        ], 'Logged in successfully');
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->success([], 'Logged out successfully');
    }

    public function me(): void
    {
        if (!$this->isAuthenticated()) {
            $this->unauthorized();
            return;
        }

        $user = $this->authService->currentUser();
        if (!$user) {
            $this->unauthorized();
            return;
        }

        $this->success(['user' => $user]);
    }
}
