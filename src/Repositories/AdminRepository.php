<?php

namespace App\Repositories;

use PDO;

class AdminRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password_hash FROM admins WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);

        $row = $stmt->fetch();
        return $row ? $row : null;
    }
}