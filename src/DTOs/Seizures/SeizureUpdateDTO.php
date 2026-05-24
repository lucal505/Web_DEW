<?php

namespace App\DTOs\Seizures;

class SeizureUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $drugName = null,
        private readonly ?float $grams = null,
        private readonly ?int $tabs = null,
        private readonly ?int $doses = null,
        private readonly ?float $mills = null,
        private readonly ?int $count = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:     isset($params['year'])  ? (int)$params['year']    : null,
            drugName: $params['drug']          ?? null,
            grams:    isset($params['grams']) ? (float)$params['grams'] : null,
            tabs:     isset($params['tabs'])  ? (int)$params['tabs']    : null,
            doses:    isset($params['doses']) ? (int)$params['doses']   : null,
            mills:    isset($params['mills']) ? (float)$params['mills'] : null,
            count:    isset($params['count']) ? (int)$params['count']   : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getDrugName(): ?string
    {
        return $this->drugName;
    }
    public function getGrams(): ?float
    {
        return $this->grams;
    }
    public function getTabs(): ?int
    {
        return $this->tabs;
    }
    public function getDoses(): ?int
    {
        return $this->doses;
    }
    public function getMills(): ?float
    {
        return $this->mills;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }

    // doar coloanele directe din drug_seizures (drug_name e tratat separat -> drug_id)
    public function toArray(): array
    {
        return array_filter([
            'year'           => $this->year,
            'grams'          => $this->grams,
            'tablets'        => $this->tabs,
            'doses_units'    => $this->doses,
            'milliliters'    => $this->mills,
            'seizures_count' => $this->count,
        ], fn($value) => $value !== null);
    }
}
