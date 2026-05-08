<?php

namespace App\Services;

use App\Repositories\EmergencyRepository;
use InvalidArgumentException;

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
        return $this->repository->getEmergencies($filters);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
