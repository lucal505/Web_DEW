<?php

namespace App\Controllers;

use App\Services\PreventionService;
use App\DTOs\Prevention\PreventionFilterDTO;
use App\DTOs\Prevention\PreventionProjectCreateDTO;
use App\DTOs\Prevention\PreventionProjectUpdateDTO;
use App\DTOs\Prevention\PreventionCampaignCreateDTO;
use App\DTOs\Prevention\PreventionCampaignUpdateDTO;
use App\DTOs\Prevention\PreventionActivityCreateDTO;
use App\DTOs\Prevention\PreventionActivityUpdateDTO;
use App\Services\ExportService;
use InvalidArgumentException;

class PreventionController extends BaseController
{
    private PreventionService $service;

    public function __construct(PreventionService $service, ?ExportService $exportService = null)
    {
        $this->service = $service;
        $this->exportService = $exportService;
    }

    // READ

    public function getProjects(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getProjects($filterDTO));
    }

    public function getCampaigns(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getCampaigns($filterDTO));
    }

    public function getActivities(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getActivities($filterDTO));
    }

    // filter options
    public function getProjectOptions(): void
    {
        $this->execute(fn() => $this->service->getProjectOptions());
    }

    public function getCampaignOptions(): void
    {
        $this->execute(fn() => $this->service->getCampaignOptions());
    }

    public function getActivityOptions(): void
    {
        $this->execute(fn() => $this->service->getActivityOptions());
    }

    // exporters
    public function exportProjects(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $data      = $this->service->getProjects($filterDTO);
        $this->handleExport($data);
    }

    public function exportCampaigns(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $data      = $this->service->getCampaigns($filterDTO);
        $this->handleExport($data);
    }

    public function exportActivities(): void
    {
        $filterDTO = PreventionFilterDTO::fromRequest($_GET);
        $data      = $this->service->getActivities($filterDTO);
        $this->handleExport($data);
    }

    // CRUD: projects
    public function createProject(): void
    {
        $this->execute(function () {
            $dto = PreventionProjectCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createProject($dto)
            ];
        });
    }

    public function updateProject(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing project id.');
            }
            $dto = PreventionProjectUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateProject($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Project not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteProject(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing project id.');
            }
            if (!$this->service->deleteProject($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Project not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: campaigns
    public function createCampaign(): void
    {
        $this->execute(function () {
            $dto = PreventionCampaignCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createCampaign($dto)
            ];
        });
    }

    public function updateCampaign(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing campaign id.');
            }
            $dto = PreventionCampaignUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateCampaign($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Campaign not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteCampaign(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing campaign id.');
            }
            if (!$this->service->deleteCampaign($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Campaign not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: activities
    public function createActivity(): void
    {
        $this->execute(function () {
            $dto = PreventionActivityCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createActivity($dto)
            ];
        });
    }

    public function updateActivity(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing activity id.');
            }
            $dto = PreventionActivityUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateActivity($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Activity not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteActivity(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing activity id.');
            }
            if (!$this->service->deleteActivity($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Activity not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }
}
