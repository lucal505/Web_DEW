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

    public function updatePassword(int $adminId, string $newHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE admins SET password_hash = :hash WHERE id = :id'
        );
        $stmt->execute([':hash' => $newHash, ':id' => $adminId]);
    }

    public function deleteByUsername(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM admins WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->rowCount() > 0;
    }
}
