<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\ValueObject;

use InvalidArgumentException;

final class AuditEvent
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('AuditEvent cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
