<?php

namespace App\Services;

use App\Repositories\SeizureRepository;
use App\DTOs\Seizures\SeizureFilterDTO;

class SeizureService extends BaseService
{
    private SeizureRepository $repository;

    public function __construct(SeizureRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getSeizures(SeizureFilterDTO $filtersDTO): array
    {
        $this->validateBaseFilters($filtersDTO);

        if ($filtersDTO->getPage() !== null) {
            $result = $this->repository->getSeizuresPaginated($filtersDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filtersDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getSeizures($filtersDTO);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
