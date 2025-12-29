<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\ValueObject;

final class AuditValues
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private readonly array $values)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->values;
    }
}
