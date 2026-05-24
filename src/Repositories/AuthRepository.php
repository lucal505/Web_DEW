<?php

namespace App\Repositories;

use PDO;

class AuthRepository extends BaseRepository
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

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
