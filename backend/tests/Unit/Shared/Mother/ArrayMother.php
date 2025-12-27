<?php
declare(strict_types=1);

namespace Tests\Unit\Shared\Mother;

final class ArrayMother
{
    /**
     * @return array<string, mixed>
     */
    public static function associative(int $items = 2): array
    {
        $data = [];

        for ($i = 0; $i < $items; $i++) {
            $data[WordMother::random()] = WordMother::random();
        }

        return $data;
    }
}
