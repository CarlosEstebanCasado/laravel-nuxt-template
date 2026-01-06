<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class UrlMother
{
    public static function random(): string
    {
        return 'https://'.WordMother::random().'.example.com/'.WordMother::random();
    }
}
