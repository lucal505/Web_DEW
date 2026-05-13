<?php

namespace App\Services;

use App\Repositories\PreventionRepository;

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

        if($this->isPaginated($filters)){
            $page=$this->getPage($filters);

            return [
                'data' => $this->repository->getProjectsPaginated($filters, $page, static::PER_PAGE),
                'pagination' => [
                    'page' => $page,
                    'per_page' => static::PER_PAGE,
                ]
            ];
        }

        return $this->repository->getProjects($filters);
    }

    public function getCampaigns(array $filters): array
    {
        $this->validateBaseFilters($filters);

        if($this->isPaginated($filters)){
            $page=$this->getPage($filters);
            return [
                'data' => $this->repository->getCampaignsPaginated($filters, $page, static::PER_PAGE),
                'pagination' => [
                    'page' => $page,
                    'per_page' => static::PER_PAGE,
                ]
            ];
        }

        return $this->repository->getCampaigns($filters);
    }

    public function getActivities(array $filters): array
    {
        $this->validateBaseFilters($filters);

        if($this->isPaginated($filters)){
            $page=$this->getPage($filters);
            return [
                'data' => $this->repository->getActivitiesPaginated($filters, $page, static::PER_PAGE),
                'pagination' => [
                    'page' => $page,
                    'per_page' => static::PER_PAGE,
                ]
            ];
        }
        return $this->repository->getActivities($filters);
    }

    public function getProjectOptions(): array
    {
        return $this->repository->getProjectOptions();
    }

    public function getCampaignOptions(): array
    {
        return $this->repository->getCampaignOptions();
    }

    public function getActivityOptions(): array
    {
        return $this->repository->getActivityOptions();
    }

    // may add more specific validation methods if needed
}
