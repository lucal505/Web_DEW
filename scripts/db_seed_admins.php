<?php

require_once __DIR__ . '/../src/Config/Database.php';
use App\Config\Database;

$pdo = Database::getInstance();

$env = parse_ini_file(__DIR__ . '/../.env');

$adminUsername = $env['ADMIN_USERNAME'] ?? null;
$adminPassword = $env['ADMIN_PASSWORD'] ?? null;

if ($adminUsername && $adminPassword) {
    $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare(
        "INSERT OR IGNORE INTO admins (username, password_hash) VALUES (:username, :hash)"
    );
    $stmt->execute([':username' => $adminUsername, ':hash' => $hash]);
    
    echo "[INFO]: Admin user seeded.<br>";
} else {
    echo "[WARNING]: No admin credentials in .env, skipping seed.<br>";
}