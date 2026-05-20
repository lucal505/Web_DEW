<?php

namespace App\DTOs\Crime;

class CrimeGeneralDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int  $id,
        public readonly int  $year,
        public readonly ?int $investigatedPersons,
        public readonly ?int $indictedPersons,
        public readonly ?int $convictedPersons,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                   (int)$row['id'],
            year:                 (int)$row['year'],
            investigatedPersons:  isset($row['investigated_persons']) ? (int)$row['investigated_persons'] : null,
            indictedPersons:      isset($row['indicted_persons'])     ? (int)$row['indicted_persons']     : null,
            convictedPersons:     isset($row['convicted_persons'])    ? (int)$row['convicted_persons']    : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'          => $this->id,
            'year'        => $this->year,
            'investigated' => $this->investigatedPersons,
            'indicted'    => $this->indictedPersons,
            'convicted'   => $this->convictedPersons,
        ], fn($v) => $v !== null);
    }
}