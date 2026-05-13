<?php

namespace App\Services;

use App\Repositories\EmergencyRepository;

class EmergencyService extends BaseService
{
    private EmergencyRepository $repository;

    public function __construct(EmergencyRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getEmergencies(array $filters): array
    {
        $this->validateBaseFilters($filters);

        if ($this->isPaginated($filters)) {
            $page = $this->getPage($filters);
            $result = $this->repository->getEmergenciesPaginated($filters, $page, static::PER_PAGE);

            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'     => $page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getEmergencies($filters);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
