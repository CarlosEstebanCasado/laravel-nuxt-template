<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\ValueObject;

final class EmailAddress
{
    public function __construct(
        private readonly string $value
    ) {
        // Keep this framework-agnostic: basic RFC-ish validation only.
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
