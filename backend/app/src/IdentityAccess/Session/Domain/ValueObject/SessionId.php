<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\ValueObject;

use InvalidArgumentException;

final class SessionId
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('SessionId cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(SessionId $other): bool
    {
        return $this->value === $other->value;
    }
}
