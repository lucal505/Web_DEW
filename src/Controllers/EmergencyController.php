<?php

namespace App\Controllers;

use App\Services\EmergencyService;
use App\DTOs\Emergency\EmergencyFilterDTO;
use App\DTOs\Emergency\EmergencyCreateDTO;
use App\DTOs\Emergency\EmergencyUpdateDTO;
use InvalidArgumentException;

class EmergencyController extends BaseController
{
    private EmergencyService $service;

    public function __construct(EmergencyService $service)
    {
        $this->service = $service;
    }

    // READ
    public function getEmergencies(): void
    {
        $filterDTO = EmergencyFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getEmergencies($filterDTO));
    }

    // filter options
    public function getOptions(): void
    {
        $this->execute(fn() => $this->service->getOptions());
    }

    public function export(): void
    {
        $filterDTO = EmergencyFilterDTO::fromRequest($_GET);
        $data      = $this->service->getEmergencies($filterDTO);
        $this->handleExport($data);
    }

    // CRUD: drug_emergencies
    public function createEmergency(): void
    {
        $this->execute(function () {
            $dto = EmergencyCreateDTO::fromRequest($this->getRequestData());
            $created = $this->service->createEmergency($dto);

            return [
                'status' => 201, 
                'body' => $created
            ];
        });
    }

    public function updateEmergency(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing emergency id.');
            }

            $dto = EmergencyUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateEmergency($id, $dto);

            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Emergency not found.']
                ];
            }

            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteEmergency(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing emergency id.');
            }

            $deleted = $this->service->deleteEmergency($id);
            if (!$deleted) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Emergency not found.']
                ];
            }

            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }
}
