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

class CrimeRepository extends BaseRepository {
    private function buildDemographicsQuerry(CrimeDemographicFilterDTO $filterDTO): array {
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

    private function buildSentencesQuerry(CrimeSentenceFilterDTO $filterDTO): array {
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

    private function buildArticlesQuerry(CrimeArticleFilterDTO $filterDTO): array {
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

    public function getDemographics(CrimeDemographicFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildDemographicsQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeDemographicDTO::fromArray($row), $rows);
    }

    public function getDemographicsPaginated(CrimeDemographicFilterDTO $filterDTO, int $page, int $perPage): array {
        [$sql, $params] = $this->buildDemographicsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeDemographicDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getSentences(CrimeSentenceFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildSentencesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeSentenceDTO::fromArray($row), $rows);
    }

    public function getSentencesPaginated(CrimeSentenceFilterDTO $filterDTO, int $page, int $perPage): array {
        [$sql, $params] = $this->buildSentencesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeSentenceDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getArticles(CrimeArticleFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildArticlesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => CrimeArticleDTO::fromArray($row), $rows);
    }

    public function getArticlesPaginated(CrimeArticleFilterDTO $filterDTO, int $page, int $perPage): array {
        [$sql, $params] = $this->buildArticlesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
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

    public function getGeneralPaginated(CrimeGeneralFilterDTO $filterDTO, int $perPage, int $page): array
    {
        [$sql, $params] = $this->buildGeneralQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
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

    public function getGroupsPaginated(CrimeGeneralFilterDTO $filterDTO, int $perPage, int $page): array
    {
        [$sql, $params] = $this->buildGroupQuery($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => CrimeGroupDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getDemographicOptions(): array {
        return [
            'years' => $this->getDistinct('crimes_demographic', 'year', 'DESC'),
            'genders' => $this->getDistinct('crimes_demographic', 'gender'),
            'age_categories' => $this->getDistinct('crimes_demographic', 'age_category')
        ];
    }

    public function getSentenceOptions(): array {
        return [
            'years' => $this->getDistinct('crimes_sentence', 'year', 'DESC'),
            'sentence_types' => $this->getDistinct('crimes_sentence', 'sentence_type')
        ];
    }

    public function getArticleOptions(): array {
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
}