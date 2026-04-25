<?php
namespace App\Controllers;

use App\Services\CrimeService;
use InvalidArgumentException;
use Exception;

class CrimeController{
    private CrimeService $service;

    public function __construct(CrimeService $service){
        $this->service=$service;
    }

    public function getDemographics(): void{
        // extrag filtrele
        $filters = array_filter($_GET, fn($value) => $value !== ''); 
        try {
            $data=$this->service->getDemographics($filters);

            http_response_code(200);
            echo json_encode($data);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Unexpected error occurred.']);
        }
    }

    public function getSentences(): void{
        $filters = array_filter($_GET, fn($value) => $value !== ''); 
        try {
            $data = $this->service->getSentences($filters);

            http_response_code(200);
            echo json_encode($data);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Unexpected error occurred.']);
        }
    }

    public function getArticles(): void{
        $filters = array_filter($_GET, fn($value) => $value !== '');
        try {
            $data = $this->service->getArticles($filters);

            http_response_code(200);
            echo json_encode($data);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Unexpected error occurred.']);    
        }
    }
}