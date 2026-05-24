<?php

namespace App\DTOs\Crime;

class CrimeGeneralUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?int $investigatedPersons = null,
        private readonly ?int $indictedPersons = null,
        private readonly ?int $convictedPersons = null,
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

    public function getYear(): ?int
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

    public function toArray(): array
    {
        return array_filter([
            'year'                 => $this->year,
            'investigated_persons' => $this->investigatedPersons,
            'indicted_persons'     => $this->indictedPersons,
            'convicted_persons'    => $this->convictedPersons,
        ], fn($value) => $value !== null);
    }
}
