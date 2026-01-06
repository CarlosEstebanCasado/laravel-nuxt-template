<?php
declare(strict_types=1);

namespace App\Src\Shared\Domain\Service;

interface ConfigProvider
{
    public function get(string $key, mixed $default = null): mixed;
}
