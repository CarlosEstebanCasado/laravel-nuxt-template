<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Security\Reauth\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\IdentityAccess\Security\Reauth\Domain\Repository\AccountRepository;

final class EloquentAccountRepository implements AccountRepository
{
    public function deleteAccount(int $userId): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        // Best-effort: revoke Sanctum personal access tokens if the app uses them.
        $user->tokens()->delete();

        $user->delete();
    }
}
