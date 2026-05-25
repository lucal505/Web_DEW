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

    public function createAdmin(string $username, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (:username, :hash)'
        );
        $stmt->execute([':username' => $username, ':hash' => $passwordHash]);
        return $stmt->rowCount() > 0;
    }

    public function updatePassword(string $username, string $newHash): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE admins SET password_hash = :hash WHERE username = :username'
        );
        $stmt->execute([':hash' => $newHash, ':username' => $username]);
        return $stmt->rowCount() > 0;
    }

    public function deleteAdmin(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM admins WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }
}
