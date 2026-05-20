<?php

namespace App\Controllers;

use App\Services\PreventionService;
use App\DTOs\Prevention\PreventionFilterDTO;


class PreventionController extends BaseController
{
    private PreventionService $service;

    public function __construct(PreventionService $service)
    {
        $this->service = $service;
    }

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
}
