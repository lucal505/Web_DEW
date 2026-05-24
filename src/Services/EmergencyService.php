<?php

namespace App\Services;

use App\Repositories\EmergencyRepository;
use App\DTOs\Emergency\EmergencyFilterDTO;
use App\DTOs\Emergency\EmergencyCreateDTO;
use App\DTOs\Emergency\EmergencyUpdateDTO;
use App\DTOs\Emergency\EmergencyDTO;
use InvalidArgumentException;


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
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getEmergenciesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'     => $filterDTO->getPage(),
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

    public function createEmergency(EmergencyCreateDTO $dto): EmergencyDTO
    {
        $this->validateEmergencyCreate($dto);
        return $this->repository->createEmergency($dto);
    }

    public function updateEmergency(int $id, EmergencyUpdateDTO $dto): ?EmergencyDTO
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Emergency id must be a positive integer.');
        }

        $this->validateEmergencyUpdate($dto);
        return $this->repository->updateEmergency($id, $dto);
    }

    public function deleteEmergency(int $id): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Emergency id must be a positive integer.');
        }

        return $this->repository->deleteEmergency($id);
    }

    private function validateEmergencyCreate(EmergencyCreateDTO $dto): void
    {
        $currentYear = (int)date('Y');
        $year = $dto->getYear();

        if ($year < $this->minYear || $year > $currentYear) {
            throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
        }

        if ($dto->getDrugType() === '' || $dto->getCategory() === '' || $dto->getValue() === '') {
            throw new InvalidArgumentException('Drug type, category, and value are required.');
        }

        if ($dto->getCount() < 0) {
            throw new InvalidArgumentException('Count must be a non-negative integer.');
        }
    }

    private function validateEmergencyUpdate(EmergencyUpdateDTO $dto): void
    {
        if (
            $dto->getYear() === null
            && $dto->getDrugType() === null
            && $dto->getCategory() === null
            && $dto->getValue() === null
            && $dto->getCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }

        $currentYear = (int)date('Y');
        if ($dto->getYear() !== null) {
            if ($dto->getYear() < $this->minYear || $dto->getYear() > $currentYear) {
                throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if ($dto->getDrugType() !== null && $dto->getDrugType() === '') {
            throw new InvalidArgumentException('Drug type cannot be empty.');
        }

        if ($dto->getCategory() !== null && $dto->getCategory() === '') {
            throw new InvalidArgumentException('Category cannot be empty.');
        }

        if ($dto->getValue() !== null && $dto->getValue() === '') {
            throw new InvalidArgumentException('Value cannot be empty.');
        }

        if ($dto->getCount() !== null && $dto->getCount() < 0) {
            throw new InvalidArgumentException('Count must be a non-negative integer.');
        }
    }
}
