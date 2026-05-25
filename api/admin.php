<?php
header('Content-Type: application/json');

// incarc autoloaderul composer pentru JWT
require_once __DIR__ . '/../vendor/autoload.php';

// load .env secrets (inainte de instantierea claselor)
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    $_ENV[$key] = $value;
}

// autoload pentru clasele din src/
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

// conexiune la baza de date
$pdo = Database::getInstance();

// folderul de uploads
$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// initializare controllere cu injectare dependinte
$authController = new AuthController(new AuthService(new AuthRepository($pdo)));

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
    ),
);

$emergencyController = new EmergencyController(
    new EmergencyService(new EmergencyRepository($pdo)),
);

$crimeController = new CrimeController(
    new CrimeService(new CrimeRepository($pdo)),
);

$preventionController = new PreventionController(
    new PreventionService(new PreventionRepository($pdo)),
);

$seizureController = new SeizureController(
    new SeizureService(new SeizureRepository($pdo)),
);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$publicActions = ['login', 'logout'];

if (!in_array($action, $publicActions)) {
    if (!$authController->requireAuth()) exit;
}


switch ($action) {

    // autentificare
    case 'login':
        $authController->login();
        break;

    case 'logout':
        // cu jwt nu avem nevoie de logout pe server
        echo json_encode(['success' => true]);
        break;

    case 'create_admin':
        $authController->createAdmin();
        break;

    case 'change_password':
        $authController->updatePassword();
        break;

    case 'delete_admin':
        $authController->deleteAdmin();
        break;

    // import
    case 'upload':
        $importController->uploadFile();
        break;

    case 'import_all':
        $importController->importAllFiles();
        break;

    case 'wipe_database':
        $wipeController->wipeDatabase();
        break;

    // CRUD: medical_emergencies
    case 'emergencies_create':
        $emergencyController->createEmergency();
        break;
    case 'emergencies_update':
        $emergencyController->updateEmergency();
        break;
    case 'emergencies_delete':
        $emergencyController->deleteEmergency();
        break;

    // CRUD: crimes_demographic
    case 'demographics_create':
        $crimeController->createDemographic();
        break;
    case 'demographics_update':
        $crimeController->updateDemographic();
        break;
    case 'demographics_delete':
        $crimeController->deleteDemographic();
        break;

    // CRUD: crimes_sentence
    case 'sentences_create':
        $crimeController->createSentence();
        break;
    case 'sentences_update':
        $crimeController->updateSentence();
        break;
    case 'sentences_delete':
        $crimeController->deleteSentence();
        break;

    // CRUD: crimes_article
    case 'articles_create':
        $crimeController->createArticle();
        break;
    case 'articles_update':
        $crimeController->updateArticle();
        break;
    case 'articles_delete':
        $crimeController->deleteArticle();
        break;

    // CRUD: crimes_general
    case 'general_create':
        $crimeController->createGeneral();
        break;
    case 'general_update':
        $crimeController->updateGeneral();
        break;
    case 'general_delete':
        $crimeController->deleteGeneral();
        break;

    // CRUD: crimes_group
    case 'groups_create':
        $crimeController->createGroup();
        break;
    case 'groups_update':
        $crimeController->updateGroup();
        break;
    case 'groups_delete':
        $crimeController->deleteGroup();
        break;

    // CRUD: prevention_projects
    case 'projects_create':
        $preventionController->createProject();
        break;
    case 'projects_update':
        $preventionController->updateProject();
        break;
    case 'projects_delete':
        $preventionController->deleteProject();
        break;

    // CRUD: prevention_campaigns
    case 'campaigns_create':
        $preventionController->createCampaign();
        break;
    case 'campaigns_update':
        $preventionController->updateCampaign();
        break;
    case 'campaigns_delete':
        $preventionController->deleteCampaign();
        break;

    // CRUD: prevention_activities
    case 'activities_create':
        $preventionController->createActivity();
        break;
    case 'activities_update':
        $preventionController->updateActivity();
        break;
    case 'activities_delete':
        $preventionController->deleteActivity();
        break;

    // CRUD: drug_seizures
    case 'seizures_create':
        $seizureController->createSeizure();
        break;
    case 'seizures_update':
        $seizureController->updateSeizure();
        break;
    case 'seizures_delete':
        $seizureController->deleteSeizure();
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
