<?php

namespace App\Controllers;

use App\Services\PreventionService;


class PreventionController extends BaseController
{
    private PreventionService $service;

    public function __construct(PreventionService $service)
    {
        $this->service = $service;
    }

    public function getProjects(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getProjects($filters));
    }

    public function getCampaigns(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getCampaigns($filters));
    }

    public function getActivities(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getActivities($filters));
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
