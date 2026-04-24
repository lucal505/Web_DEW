<?php
namespace App\Controllers;

use App\Services\EmergencyService;
use InvalidArgumentException;
use Exception;

class EmergencyController {
    private EmergencyService $service;

    public function __construct(EmergencyService $service){
        $this->service=$service;
    }

    public function getEmergencies(): void{
        $filters=array_filter($_GET, fn($value) => $value !== '');

        try {
            $data=$this->service->getEmergencies($filters);

            http_response_code(200);
            echo json_encode($data);
        } catch(InvalidArgumentException $e){
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch(Exception $e){
            http_response_code(500);
            echo json_encode(["error" => "Unexpected error occured"]);
        }
    }
}
