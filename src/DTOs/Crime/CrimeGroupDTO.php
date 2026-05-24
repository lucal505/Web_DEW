<?php

namespace App\DTOs\Crime;

class CrimeGroupDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int  $id,
        public readonly int  $year,
        public readonly ?int $identifiedGroups,
        public readonly ?int $involvedPersons,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:               (int)$row['id'],
            year:             (int)$row['year'],
            identifiedGroups: isset($row['identified_groups']) ? (int)$row['identified_groups'] : null,
            involvedPersons:  isset($row['involved_persons'])  ? (int)$row['involved_persons']  : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'       => $this->id,
            'year'     => $this->year,
            'groups'   => $this->identifiedGroups,
            'persons'  => $this->involvedPersons,
        ], fn($value) => $value !== null);
    }
}