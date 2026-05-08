<?php

namespace App\Controllers;

use App\Services\CrimeService;

class CrimeController extends BaseController
{
    private CrimeService $service;

    public function __construct(CrimeService $service)
    {
        $this->service = $service;
    }

    public function getDemographics(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getDemographics($filters));
    }

    public function getSentences(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getSentences($filters));
    }

    public function getArticles(): void
    {
        $filters = array_filter($_GET, fn($value) => $value !== '');
        $this->execute(fn() => $this->service->getArticles($filters));
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
