<?php

namespace App\Controllers;

use App\Services\SeizureService;
use App\DTOs\Seizures\SeizureFilterDTO;
use App\DTOs\Seizures\SeizureCreateDTO;
use App\DTOs\Seizures\SeizureUpdateDTO;
use App\Services\ExportService;
use InvalidArgumentException;

class SeizureController extends BaseController
{
    private SeizureService $service;

    public function __construct(SeizureService $service, ?ExportService $exportService = null)
    {
        $this->service = $service;
        $this->exportService = $exportService;
    }

    // READ
    public function getSeizures(): void
    {
        $filterDTO = SeizureFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getSeizures($filterDTO));
    }

    // filter options
    public function getOptions(): void
    {
        $this->execute(fn() => $this->service->getOptions());
    }

    // exporters
    public function export(): void
    {
        $filterDTO = SeizureFilterDTO::fromRequest($_GET);
        $data      = $this->service->getSeizures($filterDTO);
        $this->handleExport($data);
    }

    // CRUD: drug_seizures
    public function createSeizure(): void
    {
        $this->execute(function () {
            $dto = SeizureCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createSeizure($dto)
            ];
        });
    }

    public function updateSeizure(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing seizure id.');
            }
            $dto = SeizureUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateSeizure($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Seizure not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteSeizure(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing seizure id.');
            }
            if (!$this->service->deleteSeizure($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Seizure not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }
}
