<?php
session_start();
header('Content-Type: application/json');

// autoload for classes in src/
spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len     = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Database;
use App\Repositories\AdminRepository;
use App\Services\AuthService;
use App\Services\ImportManager;
use App\Controllers\AuthController;


// dependeny injection
$pdo            = Database::getInstance();
$adminRepo      = new AdminRepository($pdo);
$authService    = new AuthService($adminRepo);
$authController = new AuthController($authService);

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'login':
        $authController->login();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'check_session':
        $authController->checkSession();
        break;

    case 'upload':
        if (!$authController->requireAuth()) {
            exit;
        }

        if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File upload error.']);
            exit;
        }

        $file      = $_FILES['fileToUpload'];
        $original_name = basename($file['name']);
        $extension       = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'xls', 'xlsx'], true)) { 
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Forbidden file type.']);
            exit;
        }

        // sanitize file name to prevent directory traversal
        $safe_name   = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $original_name);
        $target_path = $upload_dir . $safe_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            try {
                (new ImportManager())->processFile($target_path);
                echo json_encode(['success' => true, 'message' => 'File uploaded and imported successfully.']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Import error: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        }
        break;

    case 'import_all':
        if (!$authController->requireAuth()) {
            exit;
        }

        try {
            (new ImportManager())->processFolder($upload_dir);
            echo json_encode(['success' => true, 'message' => 'All files imported successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}