<?php

$db_path = __DIR__ . '/../data/drugs_data.db';

if (!file_exists($db_path)) {
    exit("[ERROR]: Database not found at {$db_path}.");
}

$pdo = new PDO('sqlite:' . $db_path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$admins = [
    ['username' => 'walter',  'password' => 'pink'],
    ['username' => '_luca',  'password' => 'TomGates2608.'],
];

// in case of conflict update the password hash
$sql = $pdo->prepare(
    'INSERT INTO admins (username, password_hash)
     VALUES (:username, :password_hash)
     ON CONFLICT(username) DO UPDATE SET password_hash = excluded.password_hash'
);

foreach ($admins as $admin) {
    $hash = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $sql->execute([
        ':username'      => $admin['username'],
        ':password_hash' => $hash,
    ]);
    echo "[INFO]: Upserted admin " . $admin['username'] . "\n";
}

echo "\n[INFO]: Seeding complete.\n";