<?php
session_start();

// autload pentru clasele din src
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

$admin_username = "walter";
$admin_password_hash = '$2y$10$6eb1SUelMLisg..K/LWdxup3Ix/XHr0CiKj10.sexgkjgCkKy/Zae';

$upload_dir = __DIR__ . '/../uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$message = '';

// login 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        $_SESSION['logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $message = '<p style="color:red;">Invalid username or password.</p>';
    }
}

// upload 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        $message = '<p style="color:red;">You must be logged in to upload files.</p>';
    } else {
        $file = $_FILES['fileToUpload'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $file['tmp_name'];

            // extrag numele si previn atacuri de path traversal
            $original_name = basename($file['name']);

            // extrag si validez extensia
            $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['csv', 'xls', 'xlsx'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $message = '<p style="color:red;">ERROR: Forbidden file type (only CSV, XLS, XLSX allowed).</p>';
            } else {
                // pastrez doar caractere, cifre, punct, minus si underscore in nume
                $safe_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $original_name);

                $target_path = $upload_dir . $safe_name;

                // mut fisierul in folderul de upload
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $message = '<p style="color:green;">Fișierul ' . htmlspecialchars($safe_name) . ' a fost încărcat cu succes.</p>';

                    try{
                        // import fisierul uploadat
                        $importManager = new ImportManager();
                        $importManager->processFiles($upload_dir);
                        
                        $message .= '<p style="color:blue;">Datele au fost importate cu succes în baza de date!</p>';
                    } catch (Exception $e) {
                        $message = '<p style="color:red;">Error importing file: ' . $e->getMessage() . '</p>';
                    }
                } else {
                    $message = '<p style="color:red;">Failed to move uploaded file.</p>';
                }
            }
        } else {
            $message = '<p style="color:red;">File upload error: ' . $file['error'] . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Admin page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            background: #fff;
            padding: 20px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            border-radius: 10px;
            border-width: 2px;
        }

        button {
            background: #333;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 10px;
        }

        button:hover {
            background: #555;
        }

        .logout {
            float: right;
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .logout:hover {
            color: darkred;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Administrator Panel</h2>
        
        <?php echo $message; ?>

        <?php if (!isset($_SESSION['logged_in'])): ?>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="login">
                <label>Username:</label>
                <input type="text" name="username" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <button type="submit">Login</button>
            </form>

        <?php else: ?>
            <a href="admin.php?logout=true" class="logout">Logout</a>
            <p>You are logged in.</p>
            <hr>
            <h3>Upload Data File</h3>
            <!-- enctype permite upload de fisiere -->
            <form method="POST" action="admin.php" enctype="multipart/form-data"> 
                <input type="hidden" name="action" value="upload">
                <label>Select a CSV or Excel file:</label>
                <input type="file" name="fileToUpload" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>

                <button type="submit">Upload File</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>