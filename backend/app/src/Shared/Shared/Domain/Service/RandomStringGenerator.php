<?php

namespace App\Src\Shared\Shared\Domain\Service;

interface RandomStringGenerator
{
    public function generate(int $length): string;
}


