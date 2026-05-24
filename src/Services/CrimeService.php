<?php

namespace App\Services;

use App\Repositories\CrimeRepository;
use App\DTOs\Crime\CrimeArticleFilterDTO;
use App\DTOs\Crime\CrimeGeneralFilterDTO;
use App\DTOs\Crime\CrimeDemographicFilterDTO;
use App\DTOs\Crime\CrimeSentenceFilterDTO;
use App\DTOs\Crime\CrimeDemographicDTO;
use App\DTOs\Crime\CrimeSentenceDTO;
use App\DTOs\Crime\CrimeArticleDTO;
use App\DTOs\Crime\CrimeGeneralDTO;
use App\DTOs\Crime\CrimeGroupDTO;
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
use InvalidArgumentException;

class CrimeService extends BaseService
{
    private CrimeRepository $repository;

    public function __construct(CrimeRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getDemographics(CrimeDemographicFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getDemographicsPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getDemographics($filterDTO);
    }

    public function getSentences(CrimeSentenceFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getSentencesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getSentences($filterDTO);
    }

    public function getArticles(CrimeArticleFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getArticlesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getArticles($filterDTO);
    }

    public function getGeneral(CrimeGeneralFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);
        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getGeneralPaginated($filterDTO, static::PER_PAGE);
            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'        => $filterDTO->getPage(),
                    'per_page'    => static::PER_PAGE,
                    'total'       => $result['total'],
                    'total_pages' => (int)ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getGeneral($filterDTO);
    }

    public function getGroups(CrimeGeneralFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getGroupsPaginated($filterDTO, static::PER_PAGE);
            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'        => $filterDTO->getPage(),
                    'per_page'    => static::PER_PAGE,
                    'total'       => $result['total'],
                    'total_pages' => (int)ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getGroups($filterDTO);
    }

    public function getGeneralOptions(): array
    {
        return $this->repository->getGeneralOptions();
    }

    public function getGroupOptions(): array
    {
        return $this->repository->getGroupOptions();
    }

    // optiunile pentru filtrare
    public function getDemographicOptions(): array
    {
        return $this->repository->getDemographicOptions();
    }

    public function getSentenceOptions(): array
    {
        return $this->repository->getSentenceOptions();
    }

    public function getArticleOptions(): array
    {
        return $this->repository->getArticleOptions();
    }

    // CRUD: crimes_demographic
    public function createDemographic(CrimeDemographicCreateDTO $dto): CrimeDemographicDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getGender() === '' || $dto->getAgeCategory() === '') {
            throw new InvalidArgumentException('Gender and age category are required.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->createDemographic($dto);
    }

    public function updateDemographic(int $id, CrimeDemographicUpdateDTO $dto): ?CrimeDemographicDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getGender() === null
            && $dto->getAgeCategory() === null && $dto->getCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getGender() !== null && $dto->getGender() === '') {
            throw new InvalidArgumentException('Gender cannot be empty.');
        }
        if ($dto->getAgeCategory() !== null && $dto->getAgeCategory() === '') {
            throw new InvalidArgumentException('Age category cannot be empty.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->updateDemographic($id, $dto);
    }

    public function deleteDemographic(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteDemographic($id);
    }

    // CRUD: crimes_sentence
    public function createSentence(CrimeSentenceCreateDTO $dto): CrimeSentenceDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getSentenceType() === '' || $dto->getLawReference() === '') {
            throw new InvalidArgumentException('Sentence type and law reference are required.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->createSentence($dto);
    }

    public function updateSentence(int $id, CrimeSentenceUpdateDTO $dto): ?CrimeSentenceDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getSentenceType() === null
            && $dto->getLawReference() === null && $dto->getCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getSentenceType() !== null && $dto->getSentenceType() === '') {
            throw new InvalidArgumentException('Sentence type cannot be empty.');
        }
        if ($dto->getLawReference() !== null && $dto->getLawReference() === '') {
            throw new InvalidArgumentException('Law reference cannot be empty.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->updateSentence($id, $dto);
    }

    public function deleteSentence(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteSentence($id);
    }

    // CRUD: crimes_article
    public function createArticle(CrimeArticleCreateDTO $dto): CrimeArticleDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getLegalArticle() === '') {
            throw new InvalidArgumentException('Legal article is required.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->createArticle($dto);
    }

    public function updateArticle(int $id, CrimeArticleUpdateDTO $dto): ?CrimeArticleDTO
    {
        $this->assertId($id);
        if ($dto->getYear() === null && $dto->getLegalArticle() === null && $dto->getCount() === null) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getLegalArticle() !== null && $dto->getLegalArticle() === '') {
            throw new InvalidArgumentException('Legal article cannot be empty.');
        }
        $this->validateNonNegative($dto->getCount(), 'Count');
        return $this->repository->updateArticle($id, $dto);
    }

    public function deleteArticle(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteArticle($id);
    }

    // CRUD: crimes_general
    public function createGeneral(CrimeGeneralCreateDTO $dto): CrimeGeneralDTO
    {
        $this->validateYear($dto->getYear());
        $this->validateNonNegative($dto->getInvestigatedPersons(), 'Investigated persons');
        $this->validateNonNegative($dto->getIndictedPersons(), 'Indicted persons');
        $this->validateNonNegative($dto->getConvictedPersons(), 'Convicted persons');
        return $this->repository->createGeneral($dto);
    }

    public function updateGeneral(int $id, CrimeGeneralUpdateDTO $dto): ?CrimeGeneralDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getInvestigatedPersons() === null
            && $dto->getIndictedPersons() === null && $dto->getConvictedPersons() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        $this->validateNonNegative($dto->getInvestigatedPersons(), 'Investigated persons');
        $this->validateNonNegative($dto->getIndictedPersons(), 'Indicted persons');
        $this->validateNonNegative($dto->getConvictedPersons(), 'Convicted persons');
        return $this->repository->updateGeneral($id, $dto);
    }

    public function deleteGeneral(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteGeneral($id);
    }

    // CRUD: crimes_group
    public function createGroup(CrimeGroupCreateDTO $dto): CrimeGroupDTO
    {
        $this->validateYear($dto->getYear());
        $this->validateNonNegative($dto->getIdentifiedGroups(), 'Identified groups');
        $this->validateNonNegative($dto->getInvolvedPersons(), 'Involved persons');
        return $this->repository->createGroup($dto);
    }

    public function updateGroup(int $id, CrimeGroupUpdateDTO $dto): ?CrimeGroupDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getIdentifiedGroups() === null
            && $dto->getInvolvedPersons() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        $this->validateNonNegative($dto->getIdentifiedGroups(), 'Identified groups');
        $this->validateNonNegative($dto->getInvolvedPersons(), 'Involved persons');
        return $this->repository->updateGroup($id, $dto);
    }

    public function deleteGroup(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteGroup($id);
    }

    private function assertId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Id must be a positive integer.');
        }
    }
}
