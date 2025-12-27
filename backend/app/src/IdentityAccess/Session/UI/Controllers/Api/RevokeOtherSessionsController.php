<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\UI\Controllers\Api;

use App\Src\IdentityAccess\Session\Application\Request\RevokeOtherSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeOtherSessionsUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevokeOtherSessionsController extends Controller
{
    public function __construct(
        private readonly RevokeOtherSessionsUseCase $useCase
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

        $revoked = $this->useCase->execute(new RevokeOtherSessionsUseCaseRequest(
            userId: (int) $userId,
            currentSessionId: (string) $currentSessionId,
            url: $request->fullUrl(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return response()->json([
            'data' => [
                'revoked' => $revoked,
            ],
        ]);
    }
}


