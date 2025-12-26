<?php

namespace App\BoundedContext\Auth\User\Application\Request;

final class ResetUserPasswordUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $password,
    ) {
    }
}




