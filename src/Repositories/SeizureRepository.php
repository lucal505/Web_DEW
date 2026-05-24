<?php

namespace App\Repositories;

use App\DTOs\Seizures\SeizureDTO;
use App\DTOs\Seizures\SeizureFilterDTO;
use App\DTOs\Seizures\SeizureCreateDTO;
use App\DTOs\Seizures\SeizureUpdateDTO;
use RuntimeException;

class SeizureRepository extends BaseRepository
{
    public function wipeData(): int
    {
        $tables = [
            'drug_seizures',
            'drugs',
        ];

        $this->pdo->exec("DELETE FROM drug_seizures");
        $this->pdo->exec("DELETE FROM drugs");

        $tableList = "'" . implode("','", $tables) . "'";
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ($tableList)");

        return count($tables);
    }
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

    // gaseste drug_id dupa nume; daca drogul nu exista il creeaza
    private function resolveDrugId(string $drugName): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM drugs WHERE name = :name");
        $stmt->execute(['name' => $drugName]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int)$id;
        }

        $insert = $this->pdo->prepare("INSERT INTO drugs (name) VALUES (:name)");
        $insert->execute(['name' => $drugName]);
        return (int)$this->pdo->lastInsertId();
    }

    // aduce un seizure dupa id, cu numele drogului din JOIN
    private function fetchSeizureById(int $id): ?array
    {
        $sql = "SELECT ds.*, d.name as drug_name
                FROM drug_seizures ds
                JOIN drugs d ON ds.drug_id = d.id
                WHERE ds.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // CRUD: drug_seizures
    public function createSeizure(SeizureCreateDTO $dto): SeizureDTO
    {
        $data = $dto->toArray();
        $data['drug_id'] = $this->resolveDrugId($dto->getDrugName());

        $id  = $this->insert('drug_seizures', $data);
        $row = $this->fetchSeizureById($id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created seizure.');
        }
        return SeizureDTO::fromArray($row);
    }

    public function updateSeizure(int $id, SeizureUpdateDTO $dto): ?SeizureDTO
    {
        if ($this->fetchSeizureById($id) === null) {
            return null;
        }

        // coloanele directe din drug_seizures
        $data = $dto->toArray();

        if ($dto->getDrugName() !== null) {
            $data['drug_id'] = $this->resolveDrugId($dto->getDrugName());
        }

        $this->update('drug_seizures', $id, $data);

        $row = $this->fetchSeizureById($id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated seizure.');
        }
        return SeizureDTO::fromArray($row);
    }

    public function deleteSeizure(int $id): bool
    {
        return $this->delete('drug_seizures', $id);
    }
}
