<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\UI\Controllers\Api;

use App\Src\IdentityAccess\Session\Application\Request\RevokeSessionUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeSessionUseCase;
use App\Src\IdentityAccess\Session\Domain\Exception\CannotRevokeCurrentSession;
use App\Src\Shared\Domain\Service\Translator;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteSessionController extends Controller
{
    public function __construct(
        private readonly RevokeSessionUseCase $revokeSessionUseCase,
        private readonly Translator $translator
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => $this->translator->translate('messages.session.store_unavailable'),
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
                'message' => $this->translator->translate('messages.session.cannot_revoke_current'),
            ], 422);
        }

        if (! $revoked) {
            return response()->json([
                'message' => $this->translator->translate('messages.session.not_found'),
            ], 404);
        }

        return response()->json(status: 204);
    }
}
