<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class DateTimeMother
{
    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable;
    }
}
