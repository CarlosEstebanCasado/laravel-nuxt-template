<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowUserPreferencesController extends Controller
{
    public function __construct(
        private readonly GetUserPreferencesUseCase $getUserPreferencesUseCase
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $result = $this->getUserPreferencesUseCase->execute(
            new GetUserPreferencesUseCaseRequest(
                userId: $this->requireUserId($user)
            )
        );

        return response()->json($result);
    }
}
