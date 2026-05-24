<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    public function __construct(private AuthRepository $adminRepository) {}

    public function login(string $username, string $password): ?string
    {
        $admin = $this->adminRepository->findByUsername($username);
        $hash = $admin['password_hash'] ?? '$2y$10$invalidhashpadding000000000000000000000000000000000000';

        if (!password_verify($password, $hash) || $admin === null) {
            return null;
        }

        $payload = [
            'admin_id' => $admin['id'],
            'username' => $admin['username'],
            'exp'      => time() + 3600, // expira in 1h
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function changePassword(int $adminId, string $newPassword): void
    {
        if (empty($newPassword)) {
            throw new \InvalidArgumentException('New password cannot be empty.');
        }

        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->adminRepository->updatePassword($adminId, $hash);
    }

    public function deleteAdmin(string $username): bool
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Username is required.');
        }
        return $this->adminRepository->deleteByUsername($username);
    }
}
