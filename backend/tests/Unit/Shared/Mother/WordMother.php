<?php
declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class WordMother
{
    public static function random(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $word = '';

        for ($i = 0; $i < $length; $i++) {
            $word .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $word;
    }
}
