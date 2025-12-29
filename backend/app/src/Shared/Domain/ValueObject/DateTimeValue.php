<?php
declare(strict_types=1);

namespace App\Src\Shared\Domain\ValueObject;

final class DateTimeValue
{
    public function __construct(private readonly \DateTimeImmutable $value)
    {
    }

    public function value(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function toTimestamp(): int
    {
        return $this->value->getTimestamp();
    }
}
