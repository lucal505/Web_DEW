<?php

namespace App\DTOs\Crime;

class CrimeDemographicDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly string  $gender,
        public readonly string  $ageCategory,
        public readonly ?int    $count,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:          (int)$row['id'],
            year:        (int)$row['year'],
            gender:      $row['gender'],
            ageCategory: $row['age_category'],
            count:       isset($row['count']) ? (int)$row['count'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'     => $this->id,
            'year'   => $this->year,
            'gender' => $this->gender,
            'age'    => $this->ageCategory,
            'count'  => $this->count,
        ], fn($v) => $v !== null);
    }
}