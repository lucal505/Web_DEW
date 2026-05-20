<?php

namespace App\Controllers;

use App\Services\CrimeService;
use App\DTOs\Crime\CrimeDemographicFilterDTO;
use App\DTOs\Crime\CrimeSentenceFilterDTO;
use App\DTOs\Crime\CrimeGeneralFilterDTO;
use App\DTOs\Crime\CrimeArticleFilterDTO;

class CrimeController extends BaseController
{
    private CrimeService $service;

    public function __construct(CrimeService $service)
    {
        $this->service = $service;
    }

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
}
