<?php
namespace App\Config;

use PDO;
use PDOException;

// singleton pentru conexiunea la baza de date
class Database {
    private static $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $db_path = __DIR__ . '/../../data/drugs_data.db';
            try {
                self::$pdo = new PDO("sqlite:" . $db_path);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec("PRAGMA foreign_keys = ON");
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(["error" => "Database connection failed"]));
            }
        }
        return self::$pdo;
    }
}