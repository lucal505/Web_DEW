<?php

namespace App\Services;

use App\Repositories\PreventionRepository;
use InvalidArgumentException;

class PreventionService extends BaseService
{
    private PreventionRepository $repository;

    public function __construct(PreventionRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getProjects(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getProjects($filters);
    }

    public function getCampaigns(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getCampaigns($filters);
    }

    public function getActivities(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getActivities($filters);
    }

    // may add more specific validation methods if needed
}
