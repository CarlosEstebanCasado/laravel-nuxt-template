<?php
declare(strict_types=1);

namespace App\Src\Shared\Domain\Service;

interface RandomStringGenerator
{
    public function generate(int $length): string;
}


