<?php

namespace App\Repositories;

use PDO;

class SeizureRepository extends BaseRepository
{
    private function buildSeizuresQuery(array $filters): array
    {
        $sql = "SELECT ds.*, d.name as drug_name 
                FROM drug_seizures ds 
                JOIN drugs d ON ds.drug_id = d.id WHERE 1=1";
        $params = [];

        if (!empty($filters['drug'])) {
            $sql .= " AND d.name LIKE :drug";
            $params['drug'] = "%" . $filters['drug'] . "%";
        }

        if (!empty($filters['measurement'])) {
            $validColumns = ['grams', 'tablets', 'doses_units', 'milliliters', 'seizures_count'];
            $col = $filters['measurement'];
            if (in_array($filters['measurement'], $validColumns)) {
                $sql .= " AND ds.$col IS NOT NULL AND ds.$col > 0";
                $this->applyCommonFilters($sql, $params, $filters, 'ds.year', "ds.$col");
            } else {
                $this->applyCommonFilters($sql, $params, $filters, 'ds.year', "ds.seizures_count");
            }
        } else {
            // metoda din parinte pentru aplicare filtre
            $this->applyCommonFilters($sql, $params, $filters, 'ds.year', 'ds.seizures_count');
        }

        return [$sql, $params];
    }

    public function getSeizures(array $filters): array
    {
        [$sql, $params] = $this->buildSeizuresQuery($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getSeizuresPaginated(array $filters, int $page, int $perPage): array
    {
        [$sql, $params] = $this->buildSeizuresQuery($filters);
        $this->applyPagination($sql, $params, $page, $perPage);
        return $this->fetchAll($sql, $params);
    }

    // optiunile pentru filtrare
    public function getOptions(): array
    {
        $sqlDrugs = "SELECT DISTINCT d.name FROM drug_seizures ds JOIN drugs d ON ds.drug_id = d.id ORDER BY d.name ASC";
        return [
            'years' => $this->getDistinct('drug_seizures', 'year', 'DESC'),
            'drugs' => $this->fetchColumn($sqlDrugs)
        ];
    }
}
