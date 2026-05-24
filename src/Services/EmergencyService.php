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
        $this->validateId($id);
        $this->validateEmergencyUpdate($dto);
        return $this->repository->updateEmergency($id, $dto);
    }

    public function deleteEmergency(int $id): bool
    {
        $this->validateId($id);
        return $this->repository->deleteEmergency($id);
    }

    private function validateEmergencyCreate(EmergencyCreateDTO $dto): void
    {
        $this->validateYear($dto->getYear());

        if ($dto->getDrugType() === '' || $dto->getCategory() === '' || $dto->getValue() === '') {
            throw new InvalidArgumentException('Drug type, category, and value are required.');
        }

        $this->validateNonNegative($dto->getCount(), 'Count');
    }

    private function validateEmergencyUpdate(EmergencyUpdateDTO $dto): void
    {
        if (
            $dto->getYear() === null && $dto->getDrugType() === null
            && $dto->getCategory() === null && $dto->getValue() === null
            && $dto->getCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }

        $this->validateOptionalYear($dto->getYear());

        if ($dto->getDrugType() !== null && $dto->getDrugType() === '') {
            throw new InvalidArgumentException('Drug type cannot be empty.');
        }
        if ($dto->getCategory() !== null && $dto->getCategory() === '') {
            throw new InvalidArgumentException('Category cannot be empty.');
        }
        if ($dto->getValue() !== null && $dto->getValue() === '') {
            throw new InvalidArgumentException('Value cannot be empty.');
        }

        $this->validateNonNegative($dto->getCount(), 'Count');
    }
}
