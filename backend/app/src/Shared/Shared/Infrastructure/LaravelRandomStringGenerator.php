<?php

namespace App\BoundedContext\Shared\Shared\Infrastructure;

use App\BoundedContext\Shared\Shared\Domain\Service\RandomStringGenerator;
use Illuminate\Support\Str;

final class LaravelRandomStringGenerator implements RandomStringGenerator
{
    public function generate(int $length): string
    {
        return Str::random($length);
    }
}


