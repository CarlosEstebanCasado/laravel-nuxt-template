<?php

namespace App\BoundedContext\Auth\User\Application\UseCase;

final class UpdateUserProfileResult
{
    public function __construct(
        public readonly bool $shouldSendEmailVerificationNotification
    ) {
    }
}




