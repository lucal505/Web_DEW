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
use InvalidArgumentException;

class PreventionController extends BaseController
{
    private PreventionService $service;

    public function __construct(PreventionService $service, ?AuthController $authController = null)
    {
        $this->service = $service;
        $this->authController = $authController;
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

    // CRUD: projects
    public function createProject(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $dto = PreventionProjectCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createProject($dto)
            ];
        });
    }

    public function updateProject(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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

    public function deleteProject(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $dto = PreventionCampaignCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createCampaign($dto)
            ];
        });
    }

    public function updateCampaign(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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

    public function deleteCampaign(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $dto = PreventionActivityCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createActivity($dto)
            ];
        });
    }

    public function updateActivity(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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

    public function deleteActivity(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }
        $this->execute(function () {
            $id = $this->getIdFromRequest();
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
