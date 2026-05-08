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

    $data = [];

    // apelez servicii (controllerul da raspuns HTTP, eu vreau doar datele filtrate)
    try {
        switch ($table) {
            case 'crimes_demographic':
                $service = new CrimeService(new CrimeRepository($pdo));
                $data = $service->getDemographics($filters);
                break;
            case 'crimes_sentence':
                $service = new CrimeService(new CrimeRepository($pdo));
                $data = $service->getSentences($filters);
                break;
            case 'crimes_article':
                $service = new CrimeService(new CrimeRepository($pdo));
                $data = $service->getArticles($filters);
                break;
            case 'prevention_activities':
                $service = new PreventionService(new PreventionRepository($pdo));
                $data = $service->getActivities($filters);
                break;
            case 'prevention_campaigns':
                $service = new PreventionService(new PreventionRepository($pdo));
                $data = $service->getCampaigns($filters);
                break;
            case 'prevention_projects':
                $service = new PreventionService(new PreventionRepository($pdo));
                $data = $service->getProjects($filters);
                break;
            case 'medical_emergencies':
                $service = new EmergencyService(new EmergencyRepository($pdo));
                $data = $service->getEmergencies($filters);
                break;
            case 'drug_seizures':
                $service = new SeizureService(new SeizureRepository($pdo));
                $data = $service->getSeizures($filters);
                break;
            default:
                http_response_code(404);
                echo json_encode(["error" => "Table not found."]);
                exit;
        }
    } catch (InvalidArgumentException $e) {
        // erori de validare
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        // other
        http_response_code(500);
        echo json_encode(["error" => "Eroare la generarea exportului: " . $e->getMessage()]);
        exit;
    }

    $exportController = new ExportController();
    $exportController->exportData($data, $format);
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
