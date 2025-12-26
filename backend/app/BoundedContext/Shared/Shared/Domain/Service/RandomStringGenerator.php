<?php

namespace App\BoundedContext\Shared\Shared\Domain\Service;

interface RandomStringGenerator
{
    public function generate(int $length): string;
}


