<?php

namespace App\DTOs\Prevention;

class PreventionCampaignDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $year,
        public readonly string $campaignName,
        public readonly ?int   $beneficiariesCount,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                 (int)$row['id'],
            year:               (int)$row['year'],
            campaignName:       $row['campaign_name'],
            beneficiariesCount: isset($row['beneficiaries_count']) ? (int)$row['beneficiaries_count'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'year'  => $this->year,
            'name'  => $this->campaignName,
            'count' => $this->beneficiariesCount,
        ];
    }
}