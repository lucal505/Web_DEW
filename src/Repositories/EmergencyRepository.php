<?php
namespace App\Repositories;

class EmergencyRepository extends BaseRepository {
    
    private function buildEmergenciesQuerry(array $filters): array {
        $sql = "SELECT * FROM medical_emergencies WHERE 1=1";
        $params = [];

        if (!empty($filters['drug'])) {
            $sql .= " AND drug_type LIKE :drug";
            $params['drug'] = "%" . $filters['drug'] . "%";
        }
        if (!empty($filters['category'])) {
            $sql .= " AND category = :cat";
            $params['cat'] = $filters['category'];
        }
        if (!empty($filters['value'])) {
            $sql .= " AND value = :val";
            $params['val'] = $filters['value'];
        }

        $this->applyCommonFilters($sql, $params, $filters);
        return [$sql, $params];
    }

    public function getEmergencies(array $filters): array {
        [$sql, $params] = $this->buildEmergenciesQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getEmergenciesPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildEmergenciesQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getOptions(): array {
        return [
            'years' => $this->getDistinct('medical_emergencies', 'year', 'DESC'),
            'categories' => $this->getDistinct('medical_emergencies', 'category'),
            'drugs' => $this->getDistinct('medical_emergencies', 'drug_type')
        ];
    }
}