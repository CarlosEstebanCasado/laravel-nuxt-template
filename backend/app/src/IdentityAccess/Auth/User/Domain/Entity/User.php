<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\TwoFactorStatus;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;

final class User
{
    public function __construct(
        private readonly UserId $id,
        private readonly UserName $name,
        private readonly EmailAddress $email,
        private readonly ?DateTimeValue $emailVerifiedAt,
        private readonly AuthProvider $authProvider,
        private readonly ?DateTimeValue $passwordSetAt,
        private readonly TwoFactorStatus $twoFactorStatus,
        private readonly ?DateTimeValue $createdAt,
        private readonly ?DateTimeValue $updatedAt,
    ) {
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): UserName
    {
        return $this->name;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function emailVerifiedAt(): ?DateTimeValue
    {
        return $this->emailVerifiedAt;
    }

    public function authProvider(): AuthProvider
    {
        return $this->authProvider;
    }

    public function passwordSetAt(): ?DateTimeValue
    {
        return $this->passwordSetAt;
    }

    public function twoFactorStatus(): TwoFactorStatus
    {
        return $this->twoFactorStatus;
    }

    public function createdAt(): ?DateTimeValue
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeValue
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
