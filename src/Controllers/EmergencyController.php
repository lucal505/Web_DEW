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

    public function __construct(EmergencyService $service, ?AuthController $authController = null)
    {
        $this->service = $service;
        $this->authController = $authController;
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

    // CRUD: drug_emergencies
    public function createEmergency(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->execute(function () {
            $dto = EmergencyCreateDTO::fromRequest($this->getRequestData());
            $created = $this->service->createEmergency($dto);

            return [
                'status' => 201, 
                'body' => $created
            ];
        });
    }

    public function updateEmergency(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->execute(function () {
            $id = $this->getIdFromRequest();
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

    public function deleteEmergency(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->execute(function () {
            $id = $this->getIdFromRequest();
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
