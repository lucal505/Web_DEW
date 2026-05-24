<?php
namespace App\DTOs\Seizures;

class SeizureDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly int     $drugId,
        public readonly ?float  $grams,
        public readonly ?int    $tabs,
        public readonly ?int    $doses,
        public readonly ?float  $mills,
        public readonly ?int    $count,
        public readonly string  $drugName,
    ) {}

    // array brut PDO -> DTO
    public static function fromArray(array $row): self
    {
        return new self(
            id:       (int)$row['id'],
            year:     (int)$row['year'],
            drugId:   $row['drug_id'],
            grams: isset($row['grams'])        ? (float)$row['grams']           : null,
            tabs:  isset($row['tablets'])      ? (int)$row['tablets']           : null,
            doses: isset($row['doses_units'])  ? (int)$row['doses_units']       : null,
            mills: isset($row['milliliters'])  ? (float)$row['milliliters']     : null,
            count: isset($row['seizures_count']) ? (int)$row['seizures_count']  : null,
            drugName: $row['drug_name'],
        );
    }

    // DTO -> JSON (mascare nume coloane BD)
    public function jsonSerialize(): array
    {
        return array_filter([
            'id'    => $this->id,
            'year'  => $this->year,
            'drug'  => $this->drugName,
            'grams' => $this->grams,
            'tabs'  => $this->tabs,
            'doses' => $this->doses,
            'mills' => $this->mills,
            'count' => $this->count,
        ], fn($value) => $value !== null);
    }
}