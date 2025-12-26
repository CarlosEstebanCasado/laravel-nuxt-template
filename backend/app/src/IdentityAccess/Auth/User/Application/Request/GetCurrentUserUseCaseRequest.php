<?php

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class GetCurrentUserUseCaseRequest
{
    public function __construct(
        public readonly int $userId
    ) {
    }
}

