<?php
// setari de header (permite CORS, tipul de continut, metode permise)
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// extragem metoda HTTP folosita (GET, POST, etc.)
$http_method = $_SERVER['REQUEST_METHOD']; 

// iau ruta de la .htaccess
$route = isset($_GET['route']) ? $_GET['route'] : '';

// sparg ruta in segmente
$url_segments = explode('/', trim($route, '/'));

$resource = isset($url_segments[0]) && $url_segments[0] !== '' ? $url_segments[0] : null;

// route logic
switch ($resource) {
    case 'test':
        echo json_encode([
            "status" => "success",
            "message" => "routing works",
            "method" => $http_method
        ]);
        break;

    case 'drugs':
        if ($http_method === 'GET') {
            echo json_encode([
                "status" => "success",
                "data" => [
                    ["id" => 1, "name" => "Canabis", "category" => "Depresor"],
                    ["id" => 2, "name" => "Cocaină", "category" => "Stimulent"]
                ]
            ]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Method $http_method is not allowed."]);
        }
        break;

    default:
        http_response_code(404);
        $resource_name = $resource ? $resource : 'api_root';
        echo json_encode(["error" => "Endpoint '$resource_name' does not exist."]);
        break;
}
?>