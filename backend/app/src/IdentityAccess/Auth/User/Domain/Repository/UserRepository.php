<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Repository;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

interface UserRepository
{
    public function get(UserId $id): User;

    public function findByEmail(EmailAddress $email): ?User;

    public function createPasswordUser(
        string $name,
        EmailAddress $email,
        string $plainPassword,
        \DateTimeImmutable $passwordSetAt
    ): UserId;

    public function upsertOAuthUser(
        EmailAddress $email,
        string $name,
        string $provider,
        \DateTimeImmutable $emailVerifiedAt,
        string $plainPassword
    ): UserId;

    public function updateProfile(
        UserId $id,
        string $name,
        EmailAddress $email,
        bool $resetEmailVerification
    ): void;

    /**
     * When $passwordSetAt is null, keep the existing value untouched.
     */
    public function updatePassword(UserId $id, string $plainPassword, ?\DateTimeImmutable $passwordSetAt): void;
}




