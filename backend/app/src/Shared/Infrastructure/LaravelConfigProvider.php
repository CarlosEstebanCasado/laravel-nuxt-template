<?php

declare(strict_types=1);

namespace App\Src\Shared\Infrastructure;

use App\Src\Shared\Domain\Service\ConfigProvider;

final class LaravelConfigProvider implements ConfigProvider
{
    public function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}
