<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class GetTwoFactorRecoveryCodesUseCaseRequest
{
    public function __construct(
        public readonly int $userId
    ) {}
}
