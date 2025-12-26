<?php

namespace App\Src\Auth\User\Domain\Entity;

use App\Src\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\Auth\User\Domain\ValueObject\UserId;

final class User
{
    public function __construct(
        public readonly UserId $id,
        public readonly string $name,
        public readonly EmailAddress $email,
        public readonly ?\DateTimeImmutable $emailVerifiedAt,
        public readonly AuthProvider $authProvider,
        public readonly ?\DateTimeImmutable $passwordSetAt,
    ) {
    }

    /**
     * Step-up auth heuristic used across the app: password accounts (or accounts that have set a password)
     * must re-auth for sensitive actions.
     */
    public function requiresPasswordForSensitiveActions(): bool
    {
        return $this->authProvider->isPassword() || $this->passwordSetAt !== null;
    }
}




