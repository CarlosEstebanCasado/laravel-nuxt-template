<?php

namespace App\BoundedContext\Auth\User\Domain\Repository;

use App\BoundedContext\Auth\User\Domain\Entity\User;
use App\BoundedContext\Auth\User\Domain\ValueObject\EmailAddress;
use App\BoundedContext\Auth\User\Domain\ValueObject\UserId;

interface UserRepository
{
    public function get(UserId $id): User;

    public function findByEmail(EmailAddress $email): ?User;

    public function createPasswordUser(
        string $name,
        EmailAddress $email,
        string $passwordHash,
        \DateTimeImmutable $passwordSetAt
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
    public function updatePassword(UserId $id, string $passwordHash, ?\DateTimeImmutable $passwordSetAt): void;
}




