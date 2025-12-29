<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\ValueObject;

use InvalidArgumentException;

final class SessionLastActivity
{
    public function __construct(private readonly int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('SessionLastActivity must be a non-negative integer.');
        }
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
