<?php
namespace App\Controllers;

use App\Services\SeizureService;
use InvalidArgumentException;
use Exception;

class SeizureController {
    private SeizureService $service;

    public function __construct(SeizureService $service){
        $this->service=$service;
    }

    public function getSeizures(): void{
        $filters=array_filter($_GET, fn($value) => $value !== '');

        try {
            $data=$this->service->getSeizures($filters);

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