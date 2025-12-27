<?php
declare(strict_types=1);

namespace App\Src\Shared\Domain\Service;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}




