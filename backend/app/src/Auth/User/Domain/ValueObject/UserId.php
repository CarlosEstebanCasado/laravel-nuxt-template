<?php

namespace App\Src\Auth\User\Domain\ValueObject;

final class UserId
{
    public function __construct(
        private readonly int $value
    ) {
    }

    public function toInt(): int
    {
        return $this->value;
    }
}




