<?php

namespace App\Services;

use App\DTOs\Prevention\PreventionFilterDTO;
use App\Repositories\PreventionRepository;

class PreventionService extends BaseService
{
    private PreventionRepository $repository;

    public function __construct(PreventionRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getProjects(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO->toArray());

        if ($filterDTO->page !== null) {
            $result = $this->repository->getProjectsPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getProjects($filterDTO);
    }

    public function getCampaigns(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO->toArray());

        if ($filterDTO->page !== null) {
            $result = $this->repository->getCampaignsPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getCampaigns($filterDTO);
    }

    public function getActivities(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO->toArray());

        if ($filterDTO->page !== null) {
            $result = $this->repository->getActivitiesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }
        return $this->repository->getActivities($filterDTO);
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
