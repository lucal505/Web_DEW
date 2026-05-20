<?php
namespace App\DTOs\Emergency;

class EmergencyDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $year,
        public readonly string $drugType,
        public readonly string $category,
        public readonly string $value,
        public readonly int    $count,
    ) {}

    // array brut PDO -> DTO
    public static function fromArray(array $row): self
    {
        return new self(
            id:       (int)$row['id'],
            year:     (int)$row['year'],
            drugType: $row['drug_type'],
            category: $row['category'],
            value:    $row['value'],
            count:    (int)$row['count'],
        );
    }

    // DTO -> JSON (mascare nume coloane BD)
    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'year'     => $this->year,
            'drug'     => $this->drugType,
            'category' => $this->category,
            'value'    => $this->value,
            'count'    => $this->count,
        ];
    }
}