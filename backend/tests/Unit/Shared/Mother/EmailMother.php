<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class EmailMother
{
    public static function random(): string
    {
        return WordMother::random().'@example.com';
    }
}
