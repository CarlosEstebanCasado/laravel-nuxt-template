<?php
declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class UserAgentMother
{
    public static function random(): string
    {
        return 'Agent/'.random_int(1, 10).'.'.random_int(0, 10);
    }
}
