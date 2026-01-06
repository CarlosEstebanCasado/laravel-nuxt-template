<?php

declare(strict_types=1);

namespace App\Src\Shared\Infrastructure;

use App\Src\Shared\Domain\Service\Translator;

final class LaravelTranslator implements Translator
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $translated = __($key, $replace, $locale);

        if (is_string($translated)) {
            return $translated;
        }

        if (is_array($translated)) {
            return json_encode($translated, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $replace
     */
    public function choice(string $key, int|float $number, array $replace = [], ?string $locale = null): string
    {
        return trans_choice($key, $number, $replace, $locale);
    }
}
