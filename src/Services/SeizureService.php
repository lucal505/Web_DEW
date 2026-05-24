<?php

namespace App\Services;

use App\Repositories\SeizureRepository;
use App\DTOs\Seizures\SeizureFilterDTO;
use App\DTOs\Seizures\SeizureDTO;
use App\DTOs\Seizures\SeizureCreateDTO;
use App\DTOs\Seizures\SeizureUpdateDTO;
use InvalidArgumentException;

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

    // CRUD: drug_seizures
    public function createSeizure(SeizureCreateDTO $dto): SeizureDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getDrugName() === '') {
            throw new InvalidArgumentException('Drug name is required.');
        }

        $this->validateNonNegative($dto->getGrams(), 'Grams');
        $this->validateNonNegative($dto->getTabs(), 'Tablets');
        $this->validateNonNegative($dto->getDoses(), 'Doses');
        $this->validateNonNegative($dto->getMills(), 'Milliliters');
        $this->validateNonNegative($dto->getCount(), 'Seizures count');
        
        return $this->repository->createSeizure($dto);
    }

    public function updateSeizure(int $id, SeizureUpdateDTO $dto): ?SeizureDTO
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Seizure id must be a positive integer.');
        }

        if (
            $dto->getYear() === null && $dto->getDrugName() === null
            && $dto->getGrams() === null && $dto->getTabs() === null
            && $dto->getDoses() === null && $dto->getMills() === null
            && $dto->getCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }

        $this->validateOptionalYear($dto->getYear());
        if ($dto->getDrugName() !== null && $dto->getDrugName() === '') {
            throw new InvalidArgumentException('Drug name cannot be empty.');
        }

        $this->validateNonNegative($dto->getGrams(), 'Grams');
        $this->validateNonNegative($dto->getTabs(), 'Tablets');
        $this->validateNonNegative($dto->getDoses(), 'Doses');
        $this->validateNonNegative($dto->getMills(), 'Milliliters');
        $this->validateNonNegative($dto->getCount(), 'Seizures count');

        return $this->repository->updateSeizure($id, $dto);
    }

    public function deleteSeizure(int $id): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Seizure id must be a positive integer.');
        }
        return $this->repository->deleteSeizure($id);
    }
}
