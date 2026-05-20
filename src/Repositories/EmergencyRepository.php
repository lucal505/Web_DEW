<?php
namespace App\Repositories;

use App\DTOs\Emergency\EmergencyDTO;
use App\DTOs\Emergency\EmergencyFilterDTO;

class EmergencyRepository extends BaseRepository {
    
    private function buildEmergenciesQuery(EmergencyFilterDTO $filters): array {
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

    public function getEmergencies(EmergencyFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildEmergenciesQuery($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => EmergencyDTO::fromArray($row), $rows);
    }

    public function getEmergenciesPaginated(EmergencyFilterDTO $filterDTO, int $perPage): array {
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
    public function getOptions(): array {
        return [
            'years' => $this->getDistinct('medical_emergencies', 'year', 'DESC'),
            'types' => $this->getDistinct('medical_emergencies', 'category'),
            'drugs' => $this->getDistinct('medical_emergencies', 'drug_type')
        ];
    }
}