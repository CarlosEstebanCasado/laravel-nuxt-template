<?php

declare(strict_types=1);

namespace App\Src\Shared\Domain\ValueObject;

use InvalidArgumentException;

final class UserAgent
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('UserAgent cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
