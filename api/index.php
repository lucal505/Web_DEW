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

// extrag parametrii de rutare
$route = $_GET['route'] ?? '';
$table = $_GET['table'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// iau conexiunea la bd
$pdo = Database::getConnection();

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

// bad call
http_response_code(404);
echo json_encode(["error" => "Route or table not found."]);