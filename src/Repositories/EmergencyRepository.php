<?php

namespace App\Repositories;

use App\DTOs\Emergency\EmergencyDTO;
use App\DTOs\Emergency\EmergencyFilterDTO;
use App\DTOs\Emergency\EmergencyCreateDTO;
use App\DTOs\Emergency\EmergencyUpdateDTO;
use RuntimeException;

class EmergencyRepository extends BaseRepository
{
    public function wipeData(): int
    {
        $this->pdo->exec("DELETE FROM medical_emergencies");
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name = 'medical_emergencies'");

        return 1;
    }

    private function buildEmergenciesQuery(EmergencyFilterDTO $filters): array
    {
        $data = $filters->toArray();
        $sql = "SELECT * FROM medical_emergencies WHERE 1=1";
        $params = [];

        if (!empty($data['drug_type'])) {
            $sql .= " AND drug_type LIKE :drug_type";
            $params['drug_type'] = "%" . $data['drug_type'] . "%";
        }
        if (!empty($data['category'])) {
            $sql .= " AND category = :cat";
            $params['cat'] = $data['category'];
        }
        if (!empty($data['value'])) {
            $sql .= " AND value = :val";
            $params['val'] = $data['value'];
        }

        $this->applyCommonFilters($sql, $params, $data);
        return [$sql, $params];
    }

    // READ
    public function getEmergencies(EmergencyFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildEmergenciesQuery($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => EmergencyDTO::fromArray($row), $rows);
    }

    public function getEmergenciesPaginated(EmergencyFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildEmergenciesQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => EmergencyDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getOptions(): array
    {
        return [
            'years' => $this->getDistinct('medical_emergencies', 'year', 'DESC'),
            'types' => $this->getDistinct('medical_emergencies', 'category'),
            'drugs' => $this->getDistinct('medical_emergencies', 'drug_type')
        ];
    }

    // CRUD
    public function createEmergency(EmergencyCreateDTO $dto): EmergencyDTO
    {
        $sql = "INSERT INTO medical_emergencies (year, drug_type, category, value, count)
                VALUES (:year, :drug_type, :category, :value, :count)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year' => $dto->getYear(),
            'drug_type' => $dto->getDrugType(),
            'category' => $dto->getCategory(),
            'value' => $dto->getValue(),
            'count' => $dto->getCount(),
        ]);

        $row = $this->fetchById('medical_emergencies', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created emergency.');
        }

        return EmergencyDTO::fromArray($row);
    }

    public function updateEmergency(int $id, EmergencyUpdateDTO $dto): ?EmergencyDTO
    {
        if ($this->fetchById('medical_emergencies', $id) === null) {
            return null;
        }
        $this->applyUpdate('medical_emergencies', $id, $dto->toArray());

        $row = $this->fetchById('medical_emergencies', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated emergency.');
        }

        return EmergencyDTO::fromArray($row);
    }

    public function deleteEmergency(int $id): bool
    {        
        return $this->deleteById('medical_emergencies', $id);
    }       
}
