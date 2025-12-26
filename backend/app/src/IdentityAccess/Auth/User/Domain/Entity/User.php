<?php

namespace App\Src\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class User
{
    public function __construct(
        private readonly UserId $id,
        private readonly string $name,
        private readonly EmailAddress $email,
        private readonly ?\DateTimeImmutable $emailVerifiedAt,
        private readonly AuthProvider $authProvider,
        private readonly ?\DateTimeImmutable $passwordSetAt,
        private readonly ?\DateTimeImmutable $createdAt,
        private readonly ?\DateTimeImmutable $updatedAt,
    ) {
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function emailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function authProvider(): AuthProvider
    {
        return $this->authProvider;
    }

    public function passwordSetAt(): ?\DateTimeImmutable
    {
        return $this->passwordSetAt;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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


