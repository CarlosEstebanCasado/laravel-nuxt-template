<?php

namespace App\Src\Session\Session\UI\Controllers\Api;

use App\Src\Session\Session\Application\Request\ListUserSessionsUseCaseRequest;
use App\Src\Session\Session\Application\UseCase\ListUserSessionsUseCase;
use App\Src\Shared\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function __construct(
        private readonly ListUserSessionsUseCase $useCase
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => 'Session store is not available for this request.',
            ], 422);
        }

        $userId = $request->user()->getAuthIdentifier();
        $currentSessionId = $request->session()->getId();

        $sessions = $this->useCase->execute(new ListUserSessionsUseCaseRequest(
            userId: (int) $userId,
            currentSessionId: (string) $currentSessionId,
        ));

        return response()->json([
            'data' => $sessions,
        ]);
    }
}


