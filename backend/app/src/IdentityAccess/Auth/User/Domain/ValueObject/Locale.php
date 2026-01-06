<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\ValueObject;

use InvalidArgumentException;

final class Locale
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Locale cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
