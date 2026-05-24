<?php

namespace App\Repositories;

use App\DTOs\Crime\CrimeGeneralDTO;
use App\DTOs\Crime\CrimeGroupDTO;
use App\DTOs\Crime\CrimeDemographicFilterDTO;
use App\DTOs\Crime\CrimeSentenceFilterDTO;
use App\DTOs\Crime\CrimeGeneralFilterDTO;
use App\DTOs\Crime\CrimeArticleFilterDTO;
use App\DTOs\Crime\CrimeDemographicDTO;
use App\DTOs\Crime\CrimeSentenceDTO;
use App\DTOs\Crime\CrimeArticleDTO;
use App\DTOs\Crime\CrimeDemographicCreateDTO;
use App\DTOs\Crime\CrimeDemographicUpdateDTO;
use App\DTOs\Crime\CrimeSentenceCreateDTO;
use App\DTOs\Crime\CrimeSentenceUpdateDTO;
use App\DTOs\Crime\CrimeArticleCreateDTO;
use App\DTOs\Crime\CrimeArticleUpdateDTO;
use App\DTOs\Crime\CrimeGeneralCreateDTO;
use App\DTOs\Crime\CrimeGeneralUpdateDTO;
use App\DTOs\Crime\CrimeGroupCreateDTO;
use App\DTOs\Crime\CrimeGroupUpdateDTO;
use RuntimeException;

class CrimeRepository extends BaseRepository
{
    public function wipeData(): int
    {
        $tables = [
            'crimes_demographic',
            'crimes_sentence',
            'crimes_article',
            'crimes_general',
            'crimes_group',
        ];

        foreach ($tables as $table) {
            $this->pdo->exec("DELETE FROM $table");
        }

        $tableList = "'" . implode("','", $tables) . "'";
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ($tableList)");

        return count($tables);
    }
    
    private function buildDemographicsQuerry(CrimeDemographicFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM crimes_demographic WHERE 1=1";
        $params = [];

        if (!empty($data['gender'])) {
            $sql .= " AND gender = :gen";
            $params['gen'] = $data['gender'];
        }
        if (!empty($data['age_category'])) {
            $sql .= " AND age_category = :age";
            $params['age'] = $data['age_category'];
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $data);

        // returnez sql-ul si parametrii pentru a putea adauga paginarea daca e cazul
        return [$sql, $params];
    }

    private function buildSentencesQuerry(CrimeSentenceFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM crimes_sentence WHERE 1=1";
        $params = [];

        if (!empty($data['law'])) {
            $sql .= " AND law_reference LIKE :law";
            $params['law'] = "%" . $data['law'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $data);
        return [$sql, $params];
    }

    private function buildArticlesQuerry(CrimeArticleFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM crimes_article WHERE 1=1";
        $params = [];

        if (!empty($data['law'])) {
            $sql .= " AND legal_article LIKE :law";
            $params['law'] = "%" . $data['law'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $data);
        return [$sql, $params];
    }

    private function buildGeneralQuery(CrimeGeneralFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM crimes_general WHERE 1=1";
        $params = [];
        $this->applyCommonFilters($sql, $params, $data);
        return [$sql, $params];
    }

    private function buildGroupQuery(CrimeGeneralFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM crimes_group WHERE 1=1";
        $params = [];
        $this->applyCommonFilters($sql, $params, $data);
        return [$sql, $params];
    }

    public function getDemographics(CrimeDemographicFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildDemographicsQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeDemographicDTO::fromArray($row), $rows);
    }

    public function getDemographicsPaginated(CrimeDemographicFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildDemographicsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeDemographicDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getSentences(CrimeSentenceFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildSentencesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeSentenceDTO::fromArray($row), $rows);
    }

    public function getSentencesPaginated(CrimeSentenceFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildSentencesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeSentenceDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getArticles(CrimeArticleFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildArticlesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeArticleDTO::fromArray($row), $rows);
    }

    public function getArticlesPaginated(CrimeArticleFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildArticlesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeArticleDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getGeneral(CrimeGeneralFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildGeneralQuery($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeGeneralDTO::fromArray($row), $rows);
    }

    public function getGeneralPaginated(CrimeGeneralFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildGeneralQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeGeneralDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getGroups(CrimeGeneralFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildGroupQuery($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeGroupDTO::fromArray($row), $rows);
    }

    public function getGroupsPaginated(CrimeGeneralFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildGroupQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeGroupDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getDemographicOptions(): array
    {
        return [
            'years'     => $this->getDistinct('crimes_demographic', 'year', 'DESC'),
            'genders'   => $this->getDistinct('crimes_demographic', 'gender'),
            'ages'      => $this->getDistinct('crimes_demographic', 'age_category')
        ];
    }

    public function getSentenceOptions(): array
    {
        return [
            'years'     => $this->getDistinct('crimes_sentence', 'year', 'DESC'),
            'sentences' => $this->getDistinct('crimes_sentence', 'sentence_type')
        ];
    }

    public function getArticleOptions(): array
    {
        return ['years' => $this->getDistinct('crimes_article', 'year', 'DESC')];
    }

    public function getGeneralOptions(): array
    {
        return ['years' => $this->getDistinct('crimes_general', 'year', 'DESC')];
    }

    public function getGroupOptions(): array
    {
        return ['years' => $this->getDistinct('crimes_group', 'year', 'DESC')];
    }

    // CRUD: crimes_demographic
    public function createDemographic(CrimeDemographicCreateDTO $dto): CrimeDemographicDTO
    {
        $sql = "INSERT INTO crimes_demographic (year, gender, age_category, count)
                VALUES (:year, :gender, :age_category, :count)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dto->toArray());

        $row = $this->fetchById('crimes_demographic', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created demographic.');
        }
        return CrimeDemographicDTO::fromArray($row);
    }

    public function updateDemographic(int $id, CrimeDemographicUpdateDTO $dto): ?CrimeDemographicDTO
    {
        if ($this->fetchById('crimes_demographic', $id) === null) {
            return null;
        }
        $this->applyUpdate('crimes_demographic', $id, $dto->toArray());

        $row = $this->fetchById('crimes_demographic', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated demographic.');
        }
        return CrimeDemographicDTO::fromArray($row);
    }

    public function deleteDemographic(int $id): bool
    {
        return $this->deleteById('crimes_demographic', $id);
    }

    // CRUD: crimes_sentence
    public function createSentence(CrimeSentenceCreateDTO $dto): CrimeSentenceDTO
    {
        $sql = "INSERT INTO crimes_sentence (year, sentence_type, law_reference, count)
                VALUES (:year, :sentence_type, :law_reference, :count)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dto->toArray());

        $row = $this->fetchById('crimes_sentence', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created sentence.');
        }
        return CrimeSentenceDTO::fromArray($row);
    }

    public function updateSentence(int $id, CrimeSentenceUpdateDTO $dto): ?CrimeSentenceDTO
    {
        if ($this->fetchById('crimes_sentence', $id) === null) {
            return null;
        }
        $this->applyUpdate('crimes_sentence', $id, $dto->toArray());

        $row = $this->fetchById('crimes_sentence', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated sentence.');
        }
        return CrimeSentenceDTO::fromArray($row);
    }

    public function deleteSentence(int $id): bool
    {
        return $this->deleteById('crimes_sentence', $id);
    }

    // CRUD: crimes_article
    public function createArticle(CrimeArticleCreateDTO $dto): CrimeArticleDTO
    {
        $sql = "INSERT INTO crimes_article (year, legal_article, count)
                VALUES (:year, :legal_article, :count)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dto->toArray());

        $row = $this->fetchById('crimes_article', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created article.');
        }
        return CrimeArticleDTO::fromArray($row);
    }

    public function updateArticle(int $id, CrimeArticleUpdateDTO $dto): ?CrimeArticleDTO
    {
        if ($this->fetchById('crimes_article', $id) === null) {
            return null;
        }
        $this->applyUpdate('crimes_article', $id, $dto->toArray());

        $row = $this->fetchById('crimes_article', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated article.');
        }
        return CrimeArticleDTO::fromArray($row);
    }

    public function deleteArticle(int $id): bool
    {
        return $this->deleteById('crimes_article', $id);
    }

    // CRUD: crimes_general
    public function createGeneral(CrimeGeneralCreateDTO $dto): CrimeGeneralDTO
    {
        $sql = "INSERT INTO crimes_general (year, investigated_persons, indicted_persons, convicted_persons)
                VALUES (:year, :investigated_persons, :indicted_persons, :convicted_persons)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dto->toArray());

        $row = $this->fetchById('crimes_general', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created general record.');
        }
        return CrimeGeneralDTO::fromArray($row);
    }

    public function updateGeneral(int $id, CrimeGeneralUpdateDTO $dto): ?CrimeGeneralDTO
    {
        if ($this->fetchById('crimes_general', $id) === null) {
            return null;
        }
        $this->applyUpdate('crimes_general', $id, $dto->toArray());

        $row = $this->fetchById('crimes_general', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated general record.');
        }
        return CrimeGeneralDTO::fromArray($row);
    }

    public function deleteGeneral(int $id): bool
    {
        return $this->deleteById('crimes_general', $id);
    }

    // CRUD: crimes_group
    public function createGroup(CrimeGroupCreateDTO $dto): CrimeGroupDTO
    {
        $sql = "INSERT INTO crimes_group (year, identified_groups, involved_persons)
                VALUES (:year, :identified_groups, :involved_persons)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dto->toArray());

        $row = $this->fetchById('crimes_group', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created group.');
        }
        return CrimeGroupDTO::fromArray($row);
    }

    public function updateGroup(int $id, CrimeGroupUpdateDTO $dto): ?CrimeGroupDTO
    {
        if ($this->fetchById('crimes_group', $id) === null) {
            return null;
        }
        $this->applyUpdate('crimes_group', $id, $dto->toArray());

        $row = $this->fetchById('crimes_group', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated group.');
        }
        return CrimeGroupDTO::fromArray($row);
    }

    public function deleteGroup(int $id): bool
    {
        return $this->deleteById('crimes_group', $id);
    }
}
