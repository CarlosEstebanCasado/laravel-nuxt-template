<?php

namespace App\BoundedContext\Auth\User\Infrastructure;

use App\BoundedContext\Auth\User\Domain\Entity\User as DomainUser;
use App\BoundedContext\Auth\User\Domain\Repository\UserRepository;
use App\BoundedContext\Auth\User\Domain\ValueObject\AuthProvider;
use App\BoundedContext\Auth\User\Domain\ValueObject\EmailAddress;
use App\BoundedContext\Auth\User\Domain\ValueObject\UserId;
use App\Models\User;

final class EloquentUserRepository implements UserRepository
{
    public function get(UserId $id): DomainUser
    {
        $model = User::query()->findOrFail($id->toInt());

        return $this->toDomain($model);
    }

    public function findByEmail(EmailAddress $email): ?DomainUser
    {
        $model = User::query()->where('email', $email->toString())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function createPasswordUser(
        string $name,
        EmailAddress $email,
        string $passwordHash,
        \DateTimeImmutable $passwordSetAt
    ): UserId {
        $model = User::query()->create([
            'name' => $name,
            'email' => $email->toString(),
            'password' => $passwordHash,
            'auth_provider' => 'password',
            'password_set_at' => $passwordSetAt->format('Y-m-d H:i:s'),
        ]);

        return new UserId((int) $model->getKey());
    }

    public function upsertOAuthUser(
        EmailAddress $email,
        string $name,
        string $provider,
        \DateTimeImmutable $emailVerifiedAt,
        string $passwordHash
    ): UserId {
        /** @var User $model */
        $model = User::query()->firstOrNew(['email' => $email->toString()]);

        if (! $model->exists) {
            $model->name = $name;
            $model->password = $passwordHash;
            $model->auth_provider = $provider;
            $model->password_set_at = null;
        }

        if (is_null($model->email_verified_at)) {
            $model->email_verified_at = $emailVerifiedAt->format('Y-m-d H:i:s');
        }

        $model->save();

        return new UserId((int) $model->getKey());
    }

    public function updateProfile(
        UserId $id,
        string $name,
        EmailAddress $email,
        bool $resetEmailVerification
    ): void {
        $model = User::query()->findOrFail($id->toInt());

        $data = [
            'name' => $name,
            'email' => $email->toString(),
        ];

        if ($resetEmailVerification) {
            $data['email_verified_at'] = null;
        }

        $model->forceFill($data)->save();
    }

    public function updatePassword(UserId $id, string $passwordHash, ?\DateTimeImmutable $passwordSetAt): void
    {
        $model = User::query()->findOrFail($id->toInt());

        $data = [
            'password' => $passwordHash,
        ];

        if ($passwordSetAt !== null) {
            $data['password_set_at'] = $passwordSetAt->format('Y-m-d H:i:s');
        }

        $model->forceFill($data)->save();
    }

    private function toDomain(User $model): DomainUser
    {
        $emailVerifiedAt = $model->email_verified_at
            ? new \DateTimeImmutable($model->email_verified_at->toDateTimeString())
            : null;

        $passwordSetAt = $model->password_set_at
            ? new \DateTimeImmutable($model->password_set_at->toDateTimeString())
            : null;

        return new DomainUser(
            id: new UserId((int) $model->getKey()),
            name: (string) $model->name,
            email: new EmailAddress((string) $model->email),
            emailVerifiedAt: $emailVerifiedAt,
            authProvider: new AuthProvider((string) ($model->auth_provider ?? 'password')),
            passwordSetAt: $passwordSetAt,
        );
    }
}




