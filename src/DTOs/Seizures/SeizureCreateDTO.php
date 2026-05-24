<?php

namespace App\DTOs\Seizures;

class SeizureCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly string $drugName,
        private readonly ?float $grams,
        private readonly ?int $tabs,
        private readonly ?int $doses,
        private readonly ?float $mills,
        private readonly ?int $count,
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

    public function getYear(): int
    {
        return $this->year;
    }
    public function getDrugName(): string
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
}
