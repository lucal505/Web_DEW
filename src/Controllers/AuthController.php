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
        $token   = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

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

    public function updatePassword(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $newPassword = $body['password'] ?? '';

        // extrage admin_id din token
        $header  = apache_request_headers()['Authorization'] ?? '';
        $token   = str_replace('Bearer ', '', $header);
        $decoded = $this->authService->validateToken($token);

        if ($decoded === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token.']);
            return;
        }

        try {
            $this->authService->updatePassword($decoded->admin_id, $newPassword);
            echo json_encode(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteAdmin(): void
    {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = $body['username'] ?? '';

        try {
            $deleted = $this->authService->deleteAdmin($username);
            if (!$deleted) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Admin not found.']);
                return;
            }
            echo json_encode(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}