<?php
namespace App\Repositories;

use PDO;

class SeizureRepository extends BaseRepository {
    public function getSeizures(array $filters): array {
        $sql = "SELECT ds.*, d.name as drug_name 
                FROM drug_seizures ds 
                JOIN drugs d ON ds.drug_id = d.id WHERE 1=1";        
        $params = [];

        if (!empty($filters['drug'])) {
            $sql .= " AND d.name LIKE :drug";
            $params['drug'] = "%" . $filters['drug'] . "%";
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $filters, 'ds.year', 'ds.seizures_count');

        // execut query-ul
        return $this->fetchAll($sql, $params);
    }
}