<?php
namespace App\Controllers;

use App\Services\ExportService;

class ExportController {
    private ExportService $exportService;

    public function __construct() {
        $this->exportService = new ExportService();
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