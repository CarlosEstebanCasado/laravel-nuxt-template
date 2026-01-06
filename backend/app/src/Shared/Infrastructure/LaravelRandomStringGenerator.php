<?php

declare(strict_types=1);

namespace App\Src\Shared\Infrastructure;

use App\Src\Shared\Domain\Service\RandomStringGenerator;
use Illuminate\Support\Str;

final class LaravelRandomStringGenerator implements RandomStringGenerator
{
    public function generate(int $length): string
    {
        return Str::random($length);
    }
}
