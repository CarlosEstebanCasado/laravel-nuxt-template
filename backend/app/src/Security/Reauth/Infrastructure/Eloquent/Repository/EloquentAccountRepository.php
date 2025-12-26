<?php

namespace App\Src\Security\Reauth\Infrastructure\Eloquent\Repository;

use App\Src\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\Auth\User\Infrastructure\Eloquent\Model\User;

final class EloquentAccountRepository implements AccountRepository
{
    public function deleteAccount(int $userId): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        // Best-effort: revoke Sanctum personal access tokens if the app uses them.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $user->delete();
    }
}




