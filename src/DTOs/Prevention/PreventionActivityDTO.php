<?php

namespace App\DTOs\Prevention;

class PreventionActivityDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly string  $setting,
        public readonly ?int    $activitiesCount,
        public readonly ?int    $beneficiariesCount,
        public readonly ?string $beneficiaryType,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                 (int)$row['id'],
            year:               (int)$row['year'],
            setting:            $row['setting'],
            activitiesCount:    isset($row['activities_count']) ? (int)$row['activities_count'] : null,
            beneficiariesCount: isset($row['beneficiaries_count']) ? (int)$row['beneficiaries_count'] : null,
            beneficiaryType:    isset($row['beneficiary_type']) ? $row['beneficiary_type'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'            => $this->id,
            'year'          => $this->year,
            'set'           => $this->setting,
            'activities'    => $this->activitiesCount,
            'beneficiaries' => $this->beneficiariesCount,
            'ben_type'      => $this->beneficiaryType,
        ], fn($value) => $value !== null);
    }
}