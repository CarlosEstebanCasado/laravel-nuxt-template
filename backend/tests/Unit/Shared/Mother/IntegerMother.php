<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class IntegerMother
{
    public static function random(int $min = 1, int $max = 1_000): int
    {
        return random_int($min, $max);
    }
}
