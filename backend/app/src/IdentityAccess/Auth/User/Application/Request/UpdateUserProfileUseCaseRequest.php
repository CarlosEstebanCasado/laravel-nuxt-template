<?php

namespace App\Src\IdentityAccess\Auth\User\Application\Request;

final class UpdateUserProfileUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $isEmailChanging,
        public readonly bool $mustVerifyEmail,
    ) {
    }
}




