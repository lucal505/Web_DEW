<?php

namespace App\DTOs\Auth;

class AdminDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int    $id,
        public readonly string $username,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            username: $row['username'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
        ];
    }
}
