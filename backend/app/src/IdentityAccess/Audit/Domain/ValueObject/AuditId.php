<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\ValueObject;

use InvalidArgumentException;

final class AuditId
{
    public function __construct(private readonly int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('AuditId must be a positive integer.');
        }
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
