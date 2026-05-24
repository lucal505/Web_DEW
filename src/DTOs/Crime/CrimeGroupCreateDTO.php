<?php

namespace App\DTOs\Crime;

class CrimeGroupCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly ?int $identifiedGroups,
        private readonly ?int $involvedPersons,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:             isset($params['year'])    ? (int)$params['year']    : null,
            identifiedGroups: isset($params['groups'])  ? (int)$params['groups']  : null,
            involvedPersons:  isset($params['persons']) ? (int)$params['persons'] : null,
        );
    }

    public function getYear(): int
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
}
