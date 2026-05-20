<?php

namespace App\Repositories;

use App\DTOs\Seizures\SeizureDTO;
use App\DTOs\Seizures\SeizureFilterDTO;

class SeizureRepository extends BaseRepository
{
    private function buildSeizuresQuery(SeizureFilterDTO $filterDTO): array
    {
        $data =  $filterDTO->toArray();
        $sql = "SELECT ds.*, d.name as drug_name 
                FROM drug_seizures ds 
                JOIN drugs d ON ds.drug_id = d.id WHERE 1=1";
        $params = [];

        if (!empty($data['drug'])) {
            $sql .= " AND d.name LIKE :drug";
            $params['drug'] = "%" . $data['drug'] . "%";
        }

        if (!empty($data['measurement'])) {
            $validColumns = ['grams', 'tablets', 'doses_units', 'milliliters', 'seizures_count'];
            $col = $data['measurement'];
            if (in_array($data['measurement'], $validColumns)) {
                $sql .= " AND ds.$col IS NOT NULL AND ds.$col > 0";
                $this->applyCommonFilters($sql, $params, $data, 'ds.year', "ds.$col");
            } else {
                $this->applyCommonFilters($sql, $params, $data, 'ds.year', "ds.seizures_count");
            }
        } else {
            // metoda din parinte pentru aplicare filtre
            $this->applyCommonFilters($sql, $params, $data, 'ds.year', 'ds.seizures_count');
        }

        return [$sql, $params];
    }

    public function getSeizures(SeizureFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildSeizuresQuery($filterDTO);
        return array_map(fn($row) => SeizureDTO::fromArray($row), $this->fetchAll($sql, $params));
    }

    public function getSeizuresPaginated(SeizureFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildSeizuresQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        return [
            'data'  => array_map(fn($row) => SeizureDTO::fromArray($row), $this->fetchAll($sql, $params)),
            'total' => $total,
        ];
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
