<?php

namespace App\Src\Auth\User\Application\Request;

final class UpdateUserPasswordUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $password,
    ) {
    }
}




