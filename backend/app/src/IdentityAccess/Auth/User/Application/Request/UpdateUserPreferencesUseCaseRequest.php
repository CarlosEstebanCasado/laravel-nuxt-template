<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class UpdateUserPreferencesUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $locale,
        public readonly ?string $theme,
        public readonly ?string $primaryColor,
        public readonly ?string $neutralColor,
        public readonly ?string $timezone,
    ) {}
}
