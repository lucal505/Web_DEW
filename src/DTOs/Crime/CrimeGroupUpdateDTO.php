<?php

namespace App\DTOs\Crime;

class CrimeGroupUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?int $identifiedGroups = null,
        private readonly ?int $involvedPersons = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:             isset($params['year'])    ? (int)$params['year']    : null,
            identifiedGroups: isset($params['groups'])  ? (int)$params['groups']  : null,
            involvedPersons:  isset($params['persons']) ? (int)$params['persons'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getIdentifiedGroups(): ?int
    {
        return $this->identifiedGroups;
    }
    public function getInvolvedPersons(): ?int
    {
        return $this->involvedPersons;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'              => $this->year,
            'identified_groups' => $this->identifiedGroups,
            'involved_persons'  => $this->involvedPersons,
        ], fn($value) => $value !== null);
    }
}
