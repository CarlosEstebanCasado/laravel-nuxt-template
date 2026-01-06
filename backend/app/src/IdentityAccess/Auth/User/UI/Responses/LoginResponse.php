<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetCurrentUserUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private readonly GetCurrentUserUseCase $getCurrentUserUseCase
    ) {}

    public function toResponse($request): JsonResponse
    {
        $request = $this->assertRequestInstance($request);
        $user = $this->requireUser($request);

        $result = $this->getCurrentUserUseCase->execute(
            new GetCurrentUserUseCaseRequest(
                userId: $this->requireUserId($user),
            )
        );

        return response()->json($result);
    }

    /**
     * @param  mixed  $request
     */
    private function assertRequestInstance($request): Request
    {
        if (! $request instanceof Request) {
            throw new AuthenticationException('Invalid request instance.');
        }

        return $request;
    }

    private function requireUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated user.');
        }

        return $user;
    }

    private function requireUserId(User $user): int
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
