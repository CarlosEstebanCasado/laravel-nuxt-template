<?php
declare(strict_types=1);

namespace App\Src\Shared\UI\Controllers;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function requireUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated user.');
        }

        return $user;
    }

    protected function requireUserId(User $user): int
    {
        $userId = $user->getAuthIdentifier();

        if (! is_int($userId) && ! is_string($userId)) {
            throw new AuthenticationException('Authenticated user has an invalid identifier.');
        }

        if (! is_numeric($userId)) {
            throw new AuthenticationException('Authenticated user identifier must be numeric.');
        }

        return (int) $userId;
    }
}
