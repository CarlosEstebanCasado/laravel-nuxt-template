<?php

declare(strict_types=1);

namespace App\Src\Shared\Domain\Service;

interface Translator
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string;

    /**
     * @param  array<string, mixed>  $replace
     */
    public function choice(string $key, int|float $number, array $replace = [], ?string $locale = null): string;
}
