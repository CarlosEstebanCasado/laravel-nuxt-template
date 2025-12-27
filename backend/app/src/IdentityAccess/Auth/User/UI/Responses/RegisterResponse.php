<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetCurrentUserUseCase;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(
        private readonly GetCurrentUserUseCase $useCase
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        $result = $this->useCase->execute(new GetCurrentUserUseCaseRequest(
            userId: (int) $request->user()->getAuthIdentifier(),
        ));

        return response()->json($result, 201);
    }
}
