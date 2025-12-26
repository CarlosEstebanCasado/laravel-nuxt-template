<?php

namespace App\Src\Shared\Shared\Domain\Service;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}




