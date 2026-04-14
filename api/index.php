<?php
// setari de header (permite CORS, tipul de continut, metode permise)
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// db path
$db_path = __DIR__ . '/../data/drugs_data.db';

try {
    // connecting to db
    $pdo = new PDO("sqlite:" . $db_path);
    
    // config PDO sa arunce exceptii
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // config PDO sa returneze ca array asociativ (cheie-valoare)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // config PDO sa permita foreign keys
    $pdo->exec("PRAGMA foreign_keys = ON");

} catch (PDOException $e) {
    // eroare in caz ca nu pot sa ma conectez la baza de date
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}

// extrag ruta si metoda HTTP folosita (GET/POST..)
$route = isset($_GET['route']) ? $_GET['route'] : '';
$method = $_SERVER['REQUEST_METHOD'];

// route logic
switch ($route) {
    case 'drugs':
        if ($method === 'GET') {
            // mock
            $stmt = $pdo->query("SELECT * FROM drugs ORDER BY name ASC");
            $results = $stmt->fetchAll();
            echo json_encode($results);
        }
        break;

    case 'seizures':
        if ($method === 'GET') {
            // mock
            $sql = "SELECT ds.*, d.name as drug_name 
                    FROM drug_seizures ds 
                    JOIN drugs d ON ds.drug_id = d.id";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
        }
        break;

    default:
        echo json_encode(["message" => "Welcome to Drugs Data API. Database is connected!"]);
        break;
}
