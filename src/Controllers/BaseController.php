<?php

namespace App\Controllers;

use App\Services\ExportService;
use InvalidArgumentException;

abstract class BaseController
{

    protected ?ExportService $exportService = null;
    
    protected function handleExport(array $data): void
    {
        $format = $_GET['format'] ?? 'csv';

        if (empty($data)) {
            http_response_code(404);
            echo json_encode(['error' => 'No data to export.']);
            return;
        }

        if ($format === 'csv') {
            $this->exportService->generateCsv($data);
        } elseif ($format === 'html') {
            $this->exportService->generateHtml($data);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Unsupported format.']);
        }
    }

    protected function execute(callable $action): void
    {
        try {
            $result = $action();

            if (isset($result['status']) && isset($result['body'])) {
                http_response_code($result['status']);
                echo json_encode($result['body']);
            } else {
                http_response_code(200);
                echo json_encode($result);
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // citeste body-ul request-ului ca JSON
    protected function getRequestData(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return[];
    }
}
