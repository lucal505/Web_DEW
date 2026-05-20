<?php
header('Content-Type: application/json');

// incarc autoloaderul composer pentru JWT
require_once __DIR__ . '/../vendor/autoload.php';


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
use App\Controllers\ImportController;
use App\Repositories\AdminRepository;
use App\Services\AuthService;
use App\Services\ImportService;
use App\Controllers\AuthController;


// dependeny injection
$pdo            = Database::getInstance();
$adminRepo      = new AdminRepository($pdo);
$authService    = new AuthService($adminRepo);
$authController = new AuthController($authService);

// load .env secrets
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    $_ENV[$key] = $value;
}

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$importService  = new ImportService();
$importController = new ImportController($authController, $importService, $upload_dir);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'login':
        $authController->login();
        break;

    case 'logout':
        // cu jwt nu avem nevoie de logout pe server
        echo json_encode(['success' => true]);
        break;

    case 'upload':
        $importController->uploadFile();
        break;

    case 'import_all':
        $importController->importAllFiles();
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
