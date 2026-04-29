<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
            return;
        }

        if ($this->authService->login($username, $password)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        echo json_encode(['success' => true]);
    }

    public function checkSession(): void
    {
        echo json_encode([
            'logged_in' => $this->authService->isLoggedIn(),
            'username'  => $_SESSION['username'] ?? null,
        ]);
    }

    public function requireAuth(): bool
    {
        if (!$this->authService->isLoggedIn()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
            return false;
        }
        return true;
    }
}