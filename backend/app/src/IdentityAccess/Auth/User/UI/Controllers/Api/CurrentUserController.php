<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetCurrentUserUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function __construct(
        private readonly GetCurrentUserUseCase $getCurrentUserUseCase
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $result = $this->getCurrentUserUseCase->execute(
            new GetCurrentUserUseCaseRequest(
                userId: $this->requireUserId($user),
            )
        );

        return response()->json($result);
    }
}
