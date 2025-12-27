<?php

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

final class UpdateUserProfileResult
{
    public function __construct(
        public readonly bool $shouldSendEmailVerificationNotification
    ) {
    }
}




