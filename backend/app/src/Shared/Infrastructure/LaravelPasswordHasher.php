<?php

namespace App\Src\Shared\Infrastructure;

use App\Src\Shared\Domain\Service\PasswordHasher;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }
}




