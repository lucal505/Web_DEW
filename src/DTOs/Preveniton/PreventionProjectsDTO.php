<?php

namespace App\DTOs\Prevention;

class PreventionProjectDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $year,
        public readonly string $projectName,
        public readonly int    $beneficiariesCount,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                 (int)$row['id'],
            year:               (int)$row['year'],
            projectName:        $row['project_name'],
            beneficiariesCount: (int)$row['beneficiaries_count'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'year'  => $this->year,
            'name'  => $this->projectName,
            'count' => $this->beneficiariesCount,
        ];
    }
}