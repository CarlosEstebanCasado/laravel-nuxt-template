<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Repository;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;

interface UserRepository
{
    public function get(UserId $id): User;

    public function findByEmail(EmailAddress $email): ?User;

    public function createPasswordUser(
        UserName $name,
        EmailAddress $email,
        string $plainPassword,
        DateTimeValue $passwordSetAt
    ): UserId;

    public function upsertOAuthUser(
        EmailAddress $email,
        UserName $name,
        AuthProvider $provider,
        DateTimeValue $emailVerifiedAt,
        string $plainPassword
    ): UserId;

    public function updateProfile(
        UserId $id,
        UserName $name,
        EmailAddress $email,
        bool $resetEmailVerification
    ): void;

    /**
     * When $passwordSetAt is null, keep the existing value untouched.
     */
    public function updatePassword(UserId $id, string $plainPassword, ?DateTimeValue $passwordSetAt): void;
}


