<?php

namespace App\Src\Shared\Domain\Service;

interface RandomStringGenerator
{
    public function generate(int $length): string;
}


