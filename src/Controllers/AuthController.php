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

        $token = $this->authService->login($username, $password);

        if ($token) {
            echo json_encode(['success' => true, 'token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    }

    public function requireAuth(): bool
    {
        $headers = apache_request_headers();
        $header  = $headers['Authorization'] ?? '';
        $token   = str_replace('Bearer ', '', $header);

        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Missing token.']);
            return false;
        }

        if (!$this->authService->validateToken($token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
            return false;
        }

        return true;
    }
}