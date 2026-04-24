<?php
namespace App\Controllers;

use App\Services\PreventionService;
use InvalidArgumentException;
use Exception;

class PreventionController {
    private PreventionService $service;

    public function __construct(PreventionService $service){
        $this->service=$service;
    }

    public function getProjects(): void{
        $filters=array_filter($_GET, fn($value) => $value !== '');

        try {
            $data=$this->service->getProjects($filters);

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

    public function getCampaigns(): void{
        $filters=array_filter($_GET, fn($value) => $value !== '');

        try {
            $data=$this->service->getCampaigns($filters);

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

    public function getActivities(): void{
        $filters=array_filter($_GET, fn($value) => $value !== '');

        try {
            $data=$this->service->getActivities($filters);

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
