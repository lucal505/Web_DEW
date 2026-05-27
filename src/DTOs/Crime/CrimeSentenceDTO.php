<?php

namespace App\DTOs\Crime;

class CrimeSentenceDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly string  $sentenceType,
        public readonly string  $lawReference,
        public readonly ?int    $count,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:           (int)$row['id'],
            year:         (int)$row['year'],
            sentenceType: $row['sentence_type'],
            lawReference: $row['law_reference'],
            count:        isset($row['count']) ? (int)$row['count'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'       => $this->id,
            'year'     => $this->year,
            'sentence' => $this->sentenceType,
            'law'      => $this->lawReference,
            'count'    => $this->count,
        ], fn($value) => $value !== null);
    }

    public function exportSerialize(): array
    {
        return [
            'id'       => $this->id,
            'year'     => $this->year,
            'sentence' => $this->sentenceType,
            'law'      => $this->lawReference,
            'count'    => $this->count,
        ];
    }
}