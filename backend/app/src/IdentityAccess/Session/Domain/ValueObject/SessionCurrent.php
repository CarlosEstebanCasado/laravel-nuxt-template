<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\ValueObject;

final class SessionCurrent
{
    public function __construct(private readonly bool $value) {}

    public function value(): bool
    {
        return $this->value;
    }
}
