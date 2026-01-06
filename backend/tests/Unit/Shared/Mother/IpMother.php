<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class IpMother
{
    public static function random(): string
    {
        return implode('.', [
            random_int(1, 255),
            random_int(0, 255),
            random_int(0, 255),
            random_int(1, 254),
        ]);
    }
}
