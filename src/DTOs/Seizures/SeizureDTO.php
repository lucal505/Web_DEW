<?php
namespace App\DTOs\Seizures;

class SeizureDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly int     $drugId,
        public readonly float   $grams,
        public readonly int     $tabs,
        public readonly int     $doses,
        public readonly float   $mills,
        public readonly int     $count,
        public readonly string  $drugName,
    ) {}

    // array brut PDO -> DTO
    public static function fromArray(array $row): self
    {
        return new self(
            id:       (int)$row['id'],
            year:     (int)$row['year'],
            drugId:   $row['drug_id'],
            grams:    (float)$row['grams'],
            tabs:     (int)$row['tablets'],
            doses:    (int)$row['doses_units'],
            mills:    (float)$row['milliliters'],
            count:    (int)$row['seizures_count'],
            drugName: $row['drug_name'],
        );
    }

    // DTO -> JSON (mascare nume coloane BD)
    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'year'  => $this->year,
            'drug'  => $this->drugName,
            'grams' => $this->grams,
            'tabs'  => $this->tabs,
            'doses' => $this->doses,
            'mills' => $this->mills,
            'count' => $this->count,
        ];
    }
}