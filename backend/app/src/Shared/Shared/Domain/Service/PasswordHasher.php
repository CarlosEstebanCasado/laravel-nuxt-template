<?php

namespace App\BoundedContext\Shared\Shared\Domain\Service;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}




