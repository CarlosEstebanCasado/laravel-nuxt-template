<?php

namespace App\Src\Shared\Domain\Service;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}




