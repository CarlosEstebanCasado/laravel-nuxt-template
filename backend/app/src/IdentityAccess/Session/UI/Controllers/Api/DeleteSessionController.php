<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\UI\Controllers\Api;

use App\Src\IdentityAccess\Session\Application\Request\RevokeSessionUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeSessionUseCase;
use App\Src\IdentityAccess\Session\Domain\Exception\CannotRevokeCurrentSession;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteSessionController extends Controller
{
    public function __construct(
        private readonly RevokeSessionUseCase $revokeSessionUseCase
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => 'Session store is not available for this request.',
            ], 422);
        }

        $user = $this->requireUser($request);
        $userId = $this->requireUserId($user);
        $currentSessionId = $request->session()->getId();

        try {
            $revoked = $this->revokeSessionUseCase->execute(new RevokeSessionUseCaseRequest(
                userId: $userId,
                sessionId: $id,
                currentSessionId: (string) $currentSessionId,
                url: $request->fullUrl(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));
        } catch (CannotRevokeCurrentSession $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        if (! $revoked) {
            return response()->json([
                'message' => 'Session not found.',
            ], 404);
        }

        return response()->json(status: 204);
    }
}
