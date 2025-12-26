<?php

namespace App\Src\IdentityAccess\Session\Application\Request;

final class ListUserSessionsUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $currentSessionId,
    ) {
    }
}




