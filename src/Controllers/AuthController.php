<?php
namespace App\Controllers;

use App\DTOs\Auth\AdminCreateDTO;
use App\DTOs\Auth\AdminUpdateDTO;
use App\Services\AuthService;

class AuthController extends BaseController
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

    public function createAdmin(): void
    {
        $this->execute(function () {
            $dto = AdminCreateDTO::fromRequest($this->getRequestData());
            $admin = $this->authService->createAdmin($dto);
            return [
                'status' => 201, 
                'body' => [
                    'success' => true, 
                    'admin' => $admin
                ]   
            ];
        });
    }

    public function updatePassword(): void
    {
        $this->execute(function () {
            $dto   = AdminUpdateDTO::fromRequest($this->getRequestData());
            $admin = $this->authService->updatePassword($dto);
            if ($admin === null) {
                return [
                    'status' => 404, 
                    'body' => [
                        'success' => false, 
                        'message' => 'Admin not found.'
                    ]
                ];
            }
            return [
                'status' => 200, 
                'body' => [
                    'success' => true, 
                    'admin' => $admin
                ]
            ];
        });
    }

    public function deleteAdmin(): void
    {
        $this->execute(function () {
            $body    = $this->getRequestData();
            $deleted = $this->authService->deleteAdmin($body['username'] ?? '');
            if (!$deleted) {
                return [
                    'status' => 404, 
                    'body' => [
                        'success' => false, 
                        'message' => 'Admin not found.'
                    ]
                ];
            }
            return [
                'status' => 200,
                'body' => [
                    'success' => true
                ]
            ];
        });
    }
}