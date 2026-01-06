<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class DisableTwoFactorAuthenticationUseCaseRequest
{
    public function __construct(
        public int $userId,
    ) {}
}
