<?php

namespace App\Services;

use App\Repositories\EmergencyRepository;
use App\DTOs\Emergency\EmergencyFilterDTO;


class EmergencyService extends BaseService
{
    private EmergencyRepository $repository;

    public function __construct(EmergencyRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getEmergencies(EmergencyFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO->toArray());

        if ($filterDTO->page !== null) {
            $result = $this->repository->getEmergenciesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'     => $filterDTO->page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getEmergencies($filterDTO);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
