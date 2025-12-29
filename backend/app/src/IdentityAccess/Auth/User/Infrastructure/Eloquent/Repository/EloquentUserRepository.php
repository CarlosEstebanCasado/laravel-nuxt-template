<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\User as DomainUser;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

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
        UserName $name,
        EmailAddress $email,
        string $plainPassword,
        DateTimeValue $passwordSetAt
    ): UserId {
        $model = User::query()->create([
            'name' => $name->toString(),
            'email' => $email->toString(),
            'password' => $plainPassword,
            'auth_provider' => 'password',
            'password_set_at' => Carbon::createFromInterface($passwordSetAt->value()),
        ]);

        return new UserId($this->resolveModelId($model));
    }

    public function upsertOAuthUser(
        EmailAddress $email,
        UserName $name,
        AuthProvider $provider,
        DateTimeValue $emailVerifiedAt,
        string $plainPassword
    ): UserId {
        /** @var User $model */
        $model = User::query()->firstOrNew(['email' => $email->toString()]);

        if (! $model->exists) {
            $model->name = $name->toString();
            $model->password = $plainPassword;
            $model->auth_provider = $provider->toString();
            $model->password_set_at = null;
        }

        if (is_null($model->email_verified_at)) {
            $model->email_verified_at = Carbon::createFromInterface($emailVerifiedAt->value());
        }

        $model->save();

        return new UserId($this->resolveModelId($model));
    }

    public function updateProfile(
        UserId $id,
        UserName $name,
        EmailAddress $email,
        bool $resetEmailVerification
    ): void {
        $model = User::query()->findOrFail($id->toInt());

        $data = [
            'name' => $name->toString(),
            'email' => $email->toString(),
        ];

        if ($resetEmailVerification) {
            $data['email_verified_at'] = null;
        }

        $model->forceFill($data)->save();
    }

    public function updatePassword(UserId $id, string $plainPassword, ?DateTimeValue $passwordSetAt): void
    {
        $model = User::query()->findOrFail($id->toInt());

        $data = [
            'password' => $plainPassword,
        ];

        if ($passwordSetAt !== null) {
            $data['password_set_at'] = Carbon::createFromInterface($passwordSetAt->value());
        }

        $model->forceFill($data)->save();
    }

    private function toDomain(User $model): DomainUser
    {
        $emailVerifiedAt = $model->email_verified_at
            ? new DateTimeValue(new \DateTimeImmutable($model->email_verified_at->toDateTimeString()))
            : null;

        $passwordSetAt = $model->password_set_at
            ? new DateTimeValue(new \DateTimeImmutable($model->password_set_at->toDateTimeString()))
            : null;

        $createdAt = $model->created_at
            ? new DateTimeValue(new \DateTimeImmutable($model->created_at->toDateTimeString()))
            : null;

        $updatedAt = $model->updated_at
            ? new DateTimeValue(new \DateTimeImmutable($model->updated_at->toDateTimeString()))
            : null;

        return new DomainUser(
            id: new UserId($this->resolveModelId($model)),
            name: new UserName((string) $model->name),
            email: new EmailAddress((string) $model->email),
            emailVerifiedAt: $emailVerifiedAt,
            authProvider: new AuthProvider((string) ($model->auth_provider ?? 'password')),
            passwordSetAt: $passwordSetAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    private function resolveModelId(User $model): int
    {
        $id = $model->getKey();

        if (! is_int($id) && ! is_string($id)) {
            throw new InvalidArgumentException('User primary key must be string or int.');
        }

        if (! is_numeric($id)) {
            throw new InvalidArgumentException('User primary key must be numeric.');
        }

        return (int) $id;
    }
}
