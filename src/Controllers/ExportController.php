<?php
namespace App\Controllers;

use App\Services\ExportService;
use App\Services\CrimeService;
use App\Repositories\CrimeRepository;
use App\Services\PreventionService;
use App\Repositories\PreventionRepository;
use App\Services\EmergencyService;
use App\Repositories\EmergencyRepository;
use App\Services\SeizureService;
use App\Repositories\SeizureRepository;
use PDO;
use InvalidArgumentException;
use Exception;

class ExportController {
    private ExportService $exportService;
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->exportService = new ExportService();
        $this->pdo = $pdo;
    }

    public function handleExport(string $table, array $filters, string $format): void {
        try {
            $data = $this->getDataForTable($table, $filters);
            $this->exportData($data, $format);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Eroare la generarea exportului: " . $e->getMessage()]);
        }
    }

    private function getDataForTable(string $table, array $filters): array {
        switch ($table) {
            case 'crimes_demographic':
                return (new CrimeService(new CrimeRepository($this->pdo)))->getDemographics($filters);
            case 'crimes_sentence':
                return (new CrimeService(new CrimeRepository($this->pdo)))->getSentences($filters);
            case 'crimes_article':
                return (new CrimeService(new CrimeRepository($this->pdo)))->getArticles($filters);
            case 'prevention_activities':
                return (new PreventionService(new PreventionRepository($this->pdo)))->getActivities($filters);
            case 'prevention_campaigns':
                return (new PreventionService(new PreventionRepository($this->pdo)))->getCampaigns($filters);
            case 'prevention_projects':
                return (new PreventionService(new PreventionRepository($this->pdo)))->getProjects($filters);
            case 'medical_emergencies':
                return (new EmergencyService(new EmergencyRepository($this->pdo)))->getEmergencies($filters);
            case 'drug_seizures':
                return (new SeizureService(new SeizureRepository($this->pdo)))->getSeizures($filters);
            default:
                throw new InvalidArgumentException("Table not found.");
        }
    }

    // formats can be 'csv' or 'html'
    public function exportData(array $data, string $format) {
        if (empty($data)) {
            http_response_code(404);
            echo json_encode(["error" => "No data to export"]);
            return;
        }

        if ($format === 'csv') {
            $this->exportService->generateCsv($data);
        } elseif ($format === 'html') {
            $this->exportService->generateHtml($data);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Unsupported format"]);
        }
    }
}