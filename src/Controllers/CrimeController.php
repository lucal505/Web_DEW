<?php

namespace App\Controllers;

use App\Services\CrimeService;
use App\DTOs\Crime\CrimeDemographicFilterDTO;
use App\DTOs\Crime\CrimeSentenceFilterDTO;
use App\DTOs\Crime\CrimeGeneralFilterDTO;
use App\DTOs\Crime\CrimeArticleFilterDTO;
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

class CrimeController extends BaseController
{
    private CrimeService $service;

    public function __construct(CrimeService $service)
    {
        $this->service = $service;
    }

    // READ
    public function getDemographics(): void
    {
        $filterDTO = CrimeDemographicFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getDemographics($filterDTO));
    }

    public function getSentences(): void
    {
        $filterDTO = CrimeSentenceFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getSentences($filterDTO));
    }

    public function getArticles(): void
    {
        $filterDTO = CrimeArticleFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getArticles($filterDTO));
    }

    public function getGeneral(): void
    {
        $filterDTO = CrimeGeneralFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getGeneral($filterDTO));
    }

    public function getGroups(): void
    {
        $filterDTO = CrimeGeneralFilterDTO::fromRequest($_GET);
        $this->execute(fn() => $this->service->getGroups($filterDTO));
    }

    // filter options
    public function getGeneralOptions(): void
    {
        $this->execute(fn() => $this->service->getGeneralOptions());
    }

    public function getGroupOptions(): void
    {
        $this->execute(fn() => $this->service->getGroupOptions());
    }

    public function getDemographicOptions(): void
    {
        $this->execute(fn() => $this->service->getDemographicOptions());
    }

    public function getSentenceOptions(): void
    {
        $this->execute(fn() => $this->service->getSentenceOptions());
    }

    public function getArticleOptions(): void
    {
        $this->execute(fn() => $this->service->getArticleOptions());
    }

    public function exportDemographics(): void
    {
        $filterDTO = CrimeDemographicFilterDTO::fromRequest($_GET);
        $data      = $this->service->getDemographics($filterDTO);
        $this->handleExport($data);
    }

    // exporters
    public function exportSentences(): void
    {
        $filterDTO = CrimeSentenceFilterDTO::fromRequest($_GET);
        $data      = $this->service->getSentences($filterDTO);
        $this->handleExport($data);
    }

    public function exportArticles(): void
    {
        $filterDTO = CrimeArticleFilterDTO::fromRequest($_GET);
        $data      = $this->service->getArticles($filterDTO);
        $this->handleExport($data);
    }

    public function exportGeneral(): void
    {
        $filterDTO = CrimeGeneralFilterDTO::fromRequest($_GET);
        $data      = $this->service->getGeneral($filterDTO);
        $this->handleExport($data);
    }

    public function exportGroups(): void
    {
        $filterDTO = CrimeGeneralFilterDTO::fromRequest($_GET);
        $data      = $this->service->getGroups($filterDTO);
        $this->handleExport($data);
    }

    // CRUD: demographics
    public function createDemographic(): void
    {
        $this->execute(function () {
            $dto = CrimeDemographicCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createDemographic($dto)
            ];
        });
    }

    public function updateDemographic(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing demographic id.');
            }
            $dto = CrimeDemographicUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateDemographic($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Demographic not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteDemographic(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing demographic id.');
            }
            if (!$this->service->deleteDemographic($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Demographic not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: sentences
    public function createSentence(): void
    {
        $this->execute(function () {
            $dto = CrimeSentenceCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createSentence($dto)
            ];
        });
    }

    public function updateSentence(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing sentence id.');
            }
            $dto = CrimeSentenceUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateSentence($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Sentence not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteSentence(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing sentence id.');
            }
            if (!$this->service->deleteSentence($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Sentence not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: articles
    public function createArticle(): void
    {
        $this->execute(function () {
            $dto = CrimeArticleCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createArticle($dto)
            ];
        });
    }

    public function updateArticle(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing article id.');
            }
            $dto = CrimeArticleUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateArticle($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Article not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteArticle(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing article id.');
            }
            if (!$this->service->deleteArticle($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Article not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: general

    public function createGeneral(): void
    {
        $this->execute(function () {
            $dto = CrimeGeneralCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createGeneral($dto)
            ];
        });
    }

    public function updateGeneral(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing general record id.');
            }
            $dto = CrimeGeneralUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateGeneral($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'General record not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteGeneral(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing general record id.');
            }
            if (!$this->service->deleteGeneral($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'General record not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }

    // CRUD: groups
    public function createGroup(): void
    {
        $this->execute(function () {
            $dto = CrimeGroupCreateDTO::fromRequest($this->getRequestData());
            return [
                'status' => 201, 
                'body' => $this->service->createGroup($dto)
            ];
        });
    }

    public function updateGroup(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing group id.');
            }
            $dto = CrimeGroupUpdateDTO::fromRequest($this->getRequestData());
            $updated = $this->service->updateGroup($id, $dto);
            if ($updated === null) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Group not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => $updated
            ];
        });
    }

    public function deleteGroup(?int $id): void
    {
        $this->execute(function () use ($id) {
            if ($id === null) {
                throw new InvalidArgumentException('Missing group id.');
            }
            if (!$this->service->deleteGroup($id)) {
                return [
                    'status' => 404, 
                    'body' => ['error' => 'Group not found.']
                ];
            }
            return [
                'status' => 200, 
                'body' => ['success' => true]
            ];
        });
    }
}
