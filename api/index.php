<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// raspunde la preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// autoloader composer
require_once __DIR__ . '/../vendor/autoload.php';

// load .env
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    $_ENV[$key] = $value;
}

// autoloader pentru clasele din src/
spl_autoload_register(function ($class) {
    $prefix   = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len      = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Database;
use App\Repositories\AuthRepository;
use App\Repositories\CrimeRepository;
use App\Repositories\EmergencyRepository;
use App\Repositories\PreventionRepository;
use App\Repositories\SeizureRepository;
use App\Services\AuthService;
use App\Services\WipeService;
use App\Services\CrimeService;
use App\Services\EmergencyService;
use App\Services\PreventionService;
use App\Services\SeizureService;
use App\Services\ImportService;
use App\Controllers\AuthController;
use App\Controllers\WipeController;
use App\Controllers\ImportController;
use App\Controllers\CrimeController;
use App\Controllers\EmergencyController;
use App\Controllers\PreventionController;
use App\Controllers\SeizureController;
use App\Services\ExportService;

// extrag metoda HTTP
$method   = $_SERVER['REQUEST_METHOD'];

// parsez url (elimin parametrii, sectiunile etc.)
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = preg_replace('#^/api/?#', '', $uri); // scoate prefixul /api/
$segments = explode('/', trim($uri, '/'));

$section  = $segments[0] ?? '';
$resource = $segments[1] ?? '';

// conexiune la baza de date
$pdo = Database::getInstance();

// init controllers cu injectare dependinte
$authController = new AuthController(
    new AuthService(new AuthRepository($pdo))
);

$exportService = new ExportService();
$emergencyController = new EmergencyController(
    new EmergencyService(new EmergencyRepository($pdo)),
    $exportService
);
$crimeController = new CrimeController(
    new CrimeService(new CrimeRepository($pdo)),
    $exportService
);
$preventionController = new PreventionController(
    new PreventionService(new PreventionRepository($pdo)),
    $exportService
);
$seizureController = new SeizureController(
    new SeizureService(new SeizureRepository($pdo)),
    $exportService
);

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$importController = new ImportController(
    new ImportService(),
    $upload_dir
);

$wipeController = new WipeController(
    new WipeService(
        $pdo,
        new CrimeRepository($pdo),
        new EmergencyRepository($pdo),
        new PreventionRepository($pdo),
        new SeizureRepository($pdo)
    )
);

function requireAuth(AuthController $auth): void
{
    if (!$auth->requireAuth()) {
        exit;
    }
}

// rutare
// POST /api/auth/login (public)
if ($section === 'auth' && $resource === 'login' && $method === 'POST') {
    $authController->login();
    exit;
}

// POST /api/auth/logout (public)
if ($section === 'auth' && $resource === 'logout' && $method === 'POST') {
    echo json_encode(['success' => true]);
    exit;
}

// GET /api/filters/{resource} (public, cu filtre in query params)
if ($section === 'filters' && $method === 'GET') {
    switch ($resource) {
        case 'emergencies':
            $emergencyController->getEmergencies();
            break;
        case 'demographics':
            $crimeController->getDemographics();
            break;
        case 'sentences':
            $crimeController->getSentences();
            break;
        case 'articles':
            $crimeController->getArticles();
            break;
        case 'general':
            $crimeController->getGeneral();
            break;
        case 'groups':
            $crimeController->getGroups();
            break;
        case 'projects':
            $preventionController->getProjects();
            break;
        case 'campaigns':
            $preventionController->getCampaigns();
            break;
        case 'activities':
            $preventionController->getActivities();
            break;
        case 'seizures':
            $seizureController->getSeizures();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found.']);
    }
    exit;
}

// GET /api/options/{resource} (public, returneaza optiunile pentru filtrele din frontend)
if ($section === 'options' && $method === 'GET') {
    switch ($resource) {
        case 'emergencies':
            $emergencyController->getOptions();
            break;
        case 'demographics':
            $crimeController->getDemographicOptions();
            break;
        case 'sentences':
            $crimeController->getSentenceOptions();
            break;
        case 'articles':
            $crimeController->getArticleOptions();
            break;
        case 'general':
            $crimeController->getGeneralOptions();
            break;
        case 'groups':
            $crimeController->getGroupOptions();
            break;
        case 'projects':
            $preventionController->getProjectOptions();
            break;
        case 'campaigns':
            $preventionController->getCampaignOptions();
            break;
        case 'activities':
            $preventionController->getActivityOptions();
            break;
        case 'seizures':
            $seizureController->getOptions();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found.']);
    }
    exit;
}

// GET /api/export/{resource} (public, returneaza CSV sau HTML)
if ($section === 'export' && $method === 'GET') {
    switch ($resource) {
        case 'emergencies':
            $emergencyController->export();
            break;
        case 'demographics':
            $crimeController->exportDemographics();
            break;
        case 'sentences':
            $crimeController->exportSentences();
            break;
        case 'articles':
            $crimeController->exportArticles();
            break;
        case 'general':
            $crimeController->exportGeneral();
            break;
        case 'groups':
            $crimeController->exportGroups();
            break;
        case 'projects':
            $preventionController->exportProjects();
            break;
        case 'campaigns':
            $preventionController->exportCampaigns();
            break;
        case 'activities':
            $preventionController->exportActivities();
            break;
        case 'seizures':
            $seizureController->export();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found.']);
    }
    exit;
}

// private routes
if ($section === 'admin') {
    requireAuth($authController);

    // POST /api/admin/admins
    if ($resource === 'admins' && $method === 'POST') {
        $authController->createAdmin();
        exit;
    }

    // PATCH /api/admin/admins — schimba parola
    if ($resource === 'admins' && $method === 'PATCH') {
        // username-ul e pus in body
        $authController->updatePassword();
        exit;
    }

    // DELETE /api/admin/admins}
    if ($resource === 'admins' && $method === 'DELETE') {
        $authController->deleteAdmin();
        exit;
    }

    // import
    // POST /api/admin/import/upload
    if ($resource === 'import' && ($segments[2] ?? '') === 'upload' && $method === 'POST') {
        $importController->uploadFile();
        exit;
    }

    // POST /api/admin/import/all
    if ($resource === 'import' && ($segments[2] ?? '') === 'all' && $method === 'POST') {
        $importController->importAllFiles();
        exit;
    }

    // wipe
    // POST /api/admin/wipe
    if ($resource === 'wipe' && $method === 'DELETE') {
        $wipeController->wipeDatabase();
        exit;
    }

    // crud pe fiecare tabel
    $id = isset($segments[2]) && is_numeric($segments[2]) ? (int)$segments[2] : null;
    switch ($resource) {

        // POST   /api/admin/emergencies       
        // PATCH    /api/admin/emergencies/{id} 
        // DELETE /api/admin/emergencies/{id} 
        case 'emergencies':
            if ($method === 'POST')   $emergencyController->createEmergency();
            elseif ($method === 'PATCH')    $emergencyController->updateEmergency($id);
            elseif ($method === 'DELETE') $emergencyController->deleteEmergency($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'demographics':
            if ($method === 'POST')   $crimeController->createDemographic();
            elseif ($method === 'PATCH')    $crimeController->updateDemographic($id);
            elseif ($method === 'DELETE') $crimeController->deleteDemographic($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'sentences':
            if ($method === 'POST')   $crimeController->createSentence();
            elseif ($method === 'PATCH')    $crimeController->updateSentence($id);
            elseif ($method === 'DELETE') $crimeController->deleteSentence($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'articles':
            if ($method === 'POST')   $crimeController->createArticle();
            elseif ($method === 'PATCH')    $crimeController->updateArticle($id);
            elseif ($method === 'DELETE') $crimeController->deleteArticle($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'general':
            if ($method === 'POST')   $crimeController->createGeneral();
            elseif ($method === 'PATCH')    $crimeController->updateGeneral($id);
            elseif ($method === 'DELETE') $crimeController->deleteGeneral($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'groups':
            if ($method === 'POST')   $crimeController->createGroup();
            elseif ($method === 'PATCH')    $crimeController->updateGroup($id);
            elseif ($method === 'DELETE') $crimeController->deleteGroup($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'projects':
            if ($method === 'POST')   $preventionController->createProject();
            elseif ($method === 'PATCH')    $preventionController->updateProject($id);
            elseif ($method === 'DELETE') $preventionController->deleteProject($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'campaigns':
            if ($method === 'POST')   $preventionController->createCampaign();
            elseif ($method === 'PATCH')    $preventionController->updateCampaign($id);
            elseif ($method === 'DELETE') $preventionController->deleteCampaign($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'activities':
            if ($method === 'POST')   $preventionController->createActivity();
            elseif ($method === 'PATCH')    $preventionController->updateActivity($id);
            elseif ($method === 'DELETE') $preventionController->deleteActivity($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        case 'seizures':
            if ($method === 'POST')   $seizureController->createSeizure();
            elseif ($method === 'PATCH')    $seizureController->updateSeizure($id);
            elseif ($method === 'DELETE') $seizureController->deleteSeizure($id);
            else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed.']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found.']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found.']);
