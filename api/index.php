<?php
// setari de header (permite CORS, tipul de continut, metode permise)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

spl_autoload_register(function ($class) {
    // am folosit App\ ca namespace in fisierele din src
    $prefix = 'App\\';

    // setez directorul src/
    $base_dir = __DIR__ . '/../src/';

    // verific daca clasa are prefixul corect
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // scot App\ din numele clasei
    $relative_class = substr($class, $len);

    // construiesc calea catre clasa
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // includ fisierul daca exista 
    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Database;

use App\Repositories\CrimeRepository;
use App\Services\CrimeService;
use App\Controllers\CrimeController;

use App\Repositories\PreventionRepository;
use App\Services\PreventionService;
use App\Controllers\PreventionController;

use App\Repositories\EmergencyRepository;
use App\Services\EmergencyService;
use App\Controllers\EmergencyController;

use App\Repositories\SeizureRepository;
use App\Services\SeizureService;
use App\Controllers\SeizureController;

use App\Controllers\ExportController;

// extrag parametrii de rutare
$route = $_GET['route'] ?? '';
$table = $_GET['table'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// iau conexiunea la bd
$pdo = Database::getInstance();

if ($route === 'filters' && $method === 'GET') {

    switch ($table) {
        case 'crimes_demographic':
            $controller = new CrimeController(new CrimeService(new CrimeRepository($pdo)));
            $controller->getDemographics();
            break;

        case 'crimes_sentence':
            $controller = new CrimeController(new CrimeService(new CrimeRepository($pdo)));
            $controller->getSentences();
            break;

        case 'crimes_article':
            $controller = new CrimeController(new CrimeService(new CrimeRepository($pdo)));
            $controller->getArticles();
            break;

        case 'prevention_activities':
            $controller = new PreventionController(new PreventionService(new PreventionRepository($pdo)));
            $controller->getActivities();
            break;

        case 'prevention_campaigns':
            $controller = new PreventionController(new PreventionService(new PreventionRepository($pdo)));
            $controller->getCampaigns();
            break;

        case 'prevention_projects':
            $controller = new PreventionController(new PreventionService(new PreventionRepository($pdo)));
            $controller->getProjects();
            break;

        case 'medical_emergencies':
            $controller = new EmergencyController(new EmergencyService(new EmergencyRepository($pdo)));
            $controller->getEmergencies();
            break;

        case 'drug_seizures':
            $controller = new SeizureController(new SeizureService(new SeizureRepository($pdo)));
            $controller->getSeizures();
            break;

        default:
            http_response_code(404);
            echo json_encode(["error" => "Table not found."]);
            exit;
    }

    exit;
}

if ($route === 'export' && $method === 'GET') {
    $format = $_GET['format'] ?? 'csv';
    $filters = array_filter($_GET, fn($value, $key) => $value !== '' && $key !== 'route' && $key !== 'table' && $key !== 'format', ARRAY_FILTER_USE_BOTH);

    $exportController = new ExportController($pdo);
    $exportController->handleExport($table, $filters, $format);
    exit;
}

if ($route === 'options' && $method === 'GET') {
    switch ($table) {
        case 'crimes_demographic':
            (new CrimeController(new CrimeService(new CrimeRepository($pdo))))->getDemographicOptions();
            break;
        case 'crimes_sentence':
            (new CrimeController(new CrimeService(new CrimeRepository($pdo))))->getSentenceOptions();
            break;
        case 'crimes_article':
            (new CrimeController(new CrimeService(new CrimeRepository($pdo))))->getArticleOptions();
            break;
        case 'prevention_activities':
            (new PreventionController(new PreventionService(new PreventionRepository($pdo))))->getActivityOptions();
            break;
        case 'prevention_campaigns':
            (new PreventionController(new PreventionService(new PreventionRepository($pdo))))->getCampaignOptions();
            break;
        case 'prevention_projects':
            (new PreventionController(new PreventionService(new PreventionRepository($pdo))))->getProjectOptions();
            break;
        case 'medical_emergencies':
            (new EmergencyController(new EmergencyService(new EmergencyRepository($pdo))))->getOptions();
            break;
        case 'drug_seizures':
            (new SeizureController(new SeizureService(new SeizureRepository($pdo))))->getOptions();
            break;
        default:
            http_response_code(404);
            echo json_encode(["error" => "Table not found."]);
            break;
    }
    exit;
}
// bad call
http_response_code(404);
echo json_encode(["error" => "Route or table not found."]);
