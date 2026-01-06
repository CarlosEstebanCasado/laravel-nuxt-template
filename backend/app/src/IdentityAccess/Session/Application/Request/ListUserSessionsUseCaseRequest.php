<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\Request;

final class ListUserSessionsUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $currentSessionId,
    ) {}
}
