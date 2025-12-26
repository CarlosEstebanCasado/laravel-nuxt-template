<?php

namespace App\Src\IdentityAccess\Auth\User\Domain\ValueObject;

final class AuthProvider
{
    public function __construct(
        private readonly string $value
    ) {
        if ($this->value === '') {
            throw new \InvalidArgumentException('Auth provider cannot be empty.');
        }
    }

    public function isPassword(): bool
    {
        return $this->value === 'password';
    }

    public function toString(): string
    {
        return $this->value;
    }
}




