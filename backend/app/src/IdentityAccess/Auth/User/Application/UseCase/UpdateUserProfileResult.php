<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

final class UpdateUserProfileResult
{
    public function __construct(
        public readonly bool $shouldSendEmailVerificationNotification
    ) {}
}
