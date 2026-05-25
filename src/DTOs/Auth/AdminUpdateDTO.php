<?php

namespace App\DTOs\Auth;

class AdminUpdateDTO
{
    public function __construct(
        private readonly ?string $username = null,
        private readonly ?string $password = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            username: $params['username'] ?? null,
            password: $params['password'] ?? null,
        );
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
