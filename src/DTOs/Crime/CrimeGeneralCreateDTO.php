<?php

namespace App\DTOs\Crime;

class CrimeGeneralCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly ?int $investigatedPersons,
        private readonly ?int $indictedPersons,
        private readonly ?int $convictedPersons,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:                isset($params['year'])         ? (int)$params['year']         : null,
            investigatedPersons: isset($params['investigated']) ? (int)$params['investigated'] : null,
            indictedPersons:     isset($params['indicted'])     ? (int)$params['indicted']     : null,
            convictedPersons:    isset($params['convicted'])    ? (int)$params['convicted']    : null,
        );
    }

    public function getYear(): int
    {
        return $this->year;
    }
    public function getInvestigatedPersons(): ?int
    {
        return $this->investigatedPersons;
    }
    public function getIndictedPersons(): ?int
    {
        return $this->indictedPersons;
    }
    public function getConvictedPersons(): ?int
    {
        return $this->convictedPersons;
    }
}
