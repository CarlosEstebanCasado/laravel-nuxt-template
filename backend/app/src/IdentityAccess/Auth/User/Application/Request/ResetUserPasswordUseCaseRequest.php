<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class ResetUserPasswordUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $password,
    ) {}
}
