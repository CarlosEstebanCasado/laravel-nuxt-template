<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\ValueObject;

use InvalidArgumentException;

final class AuditTags
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('AuditTags cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
