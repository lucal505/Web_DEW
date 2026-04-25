<?php
session_start();
header('Content-Type: application/json'); // formatul de raspuns: JSON

// autoload pentru clasele din src/
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

use App\Services\ImportManager;

// hardcoded admin credentials
$admin_username = "walter";
$admin_password_hash = '$2y$10$6eb1SUelMLisg..K/LWdxup3Ix/XHr0CiKj10.sexgkjgCkKy/Zae';
$upload_dir = __DIR__ . '/../uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// logout
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// login
if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        $_SESSION['logged_in'] = true;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
    exit;
}

// upload
if ($action === 'upload') {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You must be logged in to upload files.']);
        exit;
    }

    if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File upload error.']);
        exit;
    }

    $file = $_FILES['fileToUpload'];
    $original_name = basename($file['name']);
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['csv', 'xls', 'xlsx'];

    if (!in_array($file_extension, $allowed_extensions)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Forbidden file type.']);
        exit;
    }

    // sanitize filename (pastrez doar caracterele alfanumerice, punct, dash si underscore)
    $safe_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $original_name);
    $target_path = $upload_dir . $safe_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        try {
            $importManager = new ImportManager();
            $importManager->processFiles($upload_dir);
            echo json_encode(['success' => true, 'message' => 'File uploaded and imported successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error importing file: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    }
    exit;
}

// verificare sesiune
if ($action === 'check_session') {
    echo json_encode(['logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in']]);
    exit;
}