<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use App\DTOs\Auth\AdminCreateDTO;
use App\DTOs\Auth\AdminDTO;
use App\DTOs\Auth\AdminUpdateDTO;
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

    public function createAdmin(AdminCreateDTO $dto): AdminDTO
    {
        if ($dto->getUsername() === null || $dto->getUsername() === '') {
            throw new \InvalidArgumentException('Username is required.');
        }
        if ($dto->getPassword() === null || empty($dto->getPassword())) {
            throw new \InvalidArgumentException('Password is required.');
        }
        if (strlen($dto->getPassword()) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        $hash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        try {
            $this->adminRepository->createAdmin($dto->getUsername(), $hash);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                throw new \InvalidArgumentException('Username already exists.');
            }
            throw $e;
        }

        $row = $this->adminRepository->findByUsername($dto->getUsername());
        return AdminDTO::fromArray($row);
    }

    public function updatePassword(AdminUpdateDTO $dto): ?AdminDTO
    {
        if ($dto->getUsername() === null || $dto->getUsername() === '') {
            throw new \InvalidArgumentException('Username is required.');
        }
        if ($dto->getPassword() === null || empty($dto->getPassword())) {
            throw new \InvalidArgumentException('Password is required.');
        }
        if (strlen($dto->getPassword()) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        $hash    = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        $updated = $this->adminRepository->updatePassword($dto->getUsername(), $hash);
        if (!$updated) return null;

        $row = $this->adminRepository->findByUsername($dto->getUsername());
        return AdminDTO::fromArray($row);
    }

    public function deleteAdmin(string $username): bool
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Username is required.');
        }
        return $this->adminRepository->deleteAdmin($username);
    }
}
