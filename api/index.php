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
    // mock test endpoint
    case 'test':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM drugs ORDER BY name ASC");
            $results = $stmt->fetchAll();
            echo json_encode($results);
        }
        break;

    case 'filters':
        if ($method === 'GET') {
            if (empty($_GET['table'])) {
                http_response_code(400);
                echo json_encode(["error" => "Table parameter is required. Please specify a table to filter."]);
                exit; //opresc executia
            }
            $table = $_GET['table'];

            // verific daca tabela respectiva exista in bd si daca nu e o tabela de sistem (sqlite_*)
            // pentru a preveni atacuri sau alte erori
            $stmt_check = $pdo->prepare("
                SELECT name 
                FROM sqlite_master 
                WHERE type='table' 
                AND name = :tableName 
                AND name NOT LIKE 'sqlite_%'
            ");
            $stmt_check->execute(['tableName' => $table]);

            if (!$stmt_check->fetchColumn()) {
                http_response_code(404);
                echo json_encode(["error" => "Table '$table' does not exist in the database or is forbidden."]);
                exit;
            }

            // interogarea de baza
            $sql = "SELECT * FROM $table WHERE 1=1";
            $params = [];

            // default year and count column names
            $year_column = 'year';
            $count_column = 'count';

            // logica specifica pe tabele
            if ($table === 'medical_emergencies') {
                if (!empty($_GET['drug'])) {
                    $sql .= " AND drug_type LIKE :drug";
                    $params['drug'] = "%" . $_GET['drug'] . "%";
                }
                if (!empty($_GET['category'])) {
                    $sql .= " AND category = :cat";
                    $params['cat'] = $_GET['category'];
                }
                if (!empty($_GET['value'])) {
                    $sql .= " AND value = :val";
                    $params['val'] = $_GET['value'];
                }
            } elseif ($table === 'drug_seizures') {
                // suprascriu SQL pentru ca e nevoie de join
                $sql = "SELECT ds.*, d.name as drug_name FROM drug_seizures ds 
                        JOIN drugs d ON ds.drug_id = d.id WHERE 1=1";

                // aici coloana year si count are alt nume deci suprascriu
                $year_column = 'ds.year';
                $count_column = 'ds.seizures_count';

                if (!empty($_GET['drug'])) {
                    $sql .= " AND d.name LIKE :drug";
                    $params['drug'] = "%" . $_GET['drug'] . "%";
                }
            } elseif ($table === 'crimes_demographic') {
                if (!empty($_GET['gender'])) {
                    $sql .= " AND gender = :gen";
                    $params['gen'] = $_GET['gender'];
                }
                if (!empty($_GET['age_category'])) {
                    $sql .= " AND age_category = :age";
                    $params['age'] = $_GET['age_category'];
                }
            } elseif ($table === 'crimes_article') {
                if (!empty($_GET['law'])) {
                    $sql .= " AND legal_article LIKE :law";
                    $params['law'] = "%" . $_GET['law'] . "%";
                }
            } elseif (in_array($table, ['prevention_projects', 'prevention_campaigns'])) {
                $name_column = ($table === 'prevention_projects') ? 'project_name' : 'campaign_name';

                // aici coloana count are alt nume deci suprascriu
                $count_column = 'beneficiaries_count';

                if (!empty($_GET['name'])) {
                    $sql .= " AND $name_column LIKE :name";
                    $params['name'] = "%" . $_GET['name'] . "%";
                }
            } elseif ($table==='prevention_activities') {
                if (!empty($_GET['count_by'])) {
                    $count_column = $_GET['count_by'];
                } else {
                    $count_column = 'beneficiaries_count'; // default count column
                }

                if(!empty($_GET['setting'])) {
                    $sql .= " AND setting LIKE :setting";
                    $params['setting'] = "%" . $_GET['setting'] . "%";
                }

                if (!empty($_GET['beneficiary_type'])) {
                    $sql .= " AND beneficiary_type LIKE :btype";
                    $params['btype'] = "%" . $_GET['beneficiary_type'] . "%";
                }
            } elseif ($table === 'crimes_sentence') {
                if (!empty($_GET['law'])) {
                    $sql .= " AND law_reference LIKE :law";
                    $params['law'] = "%" . $_GET['law'] . "%";
                }
                if (!empty($_GET['sentence_type'])) {
                    $sql .= " AND sentence_type = :stype";
                    $params['stype'] = $_GET['sentence_type'];
                }
            }

            // extrag variabilele comune pentru toate tabelele
            $year      = isset($_GET['year']) ? (int)$_GET['year'] : null;
            $count     = isset($_GET['count']) ? (int)$_GET['count'] : null;
            $min_count = isset($_GET['min_count']) ? (int)$_GET['min_count'] : null;
            $max_count = isset($_GET['max_count']) ? (int)$_GET['max_count'] : null;

            // aplic filtrele comune
            if ($year) {
                $sql .= " AND $year_column = :year";
                $params['year'] = $year;
            }
            if ($count) {
                $sql .= " AND $count_column = :cnt";
                $params['cnt'] = $count;
            }
            if ($min_count) {
                $sql .= " AND $count_column >= :min_cnt";
                $params['min_cnt'] = $min_count;
            }
            if ($max_count) {
                $sql .= " AND $count_column <= :max_cnt";
                $params['max_cnt'] = $max_count;
            }

            // returnez datele din db
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $results = $stmt->fetchAll();

                echo json_encode($results);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "eroare la interogarea bazei de date: " . $e->getMessage()]);
            }
        }
        break;

    default:
        echo json_encode(["message" => "Welcome to Drugs Data API. Database is connected!"]);
        break;
}
