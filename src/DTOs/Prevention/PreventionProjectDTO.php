<?php

namespace App\DTOs\Prevention;

class PreventionProjectDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $year,
        public readonly string $projectName,
        public readonly ?int   $beneficiariesCount,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                 (int)$row['id'],
            year:               (int)$row['year'],
            projectName:        $row['project_name'],
            beneficiariesCount: isset($row['beneficiaries_count']) ? (int)$row['beneficiaries_count'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'    => $this->id,
            'year'  => $this->year,
            'name'  => $this->projectName,
            'count' => $this->beneficiariesCount,
        ], fn($value) => $value !== null);
    }

    public function exportSerialize(): array
    {
        return [
            'id'    => $this->id,
            'year'  => $this->year,
            'name'  => $this->projectName,
            'count' => $this->beneficiariesCount,
        ];
    }
}