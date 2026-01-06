<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\UI\Controllers\Api;

use App\Src\IdentityAccess\Session\Application\Request\RevokeOtherSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeOtherSessionsUseCase;
use App\Src\Shared\Domain\Service\Translator;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevokeOtherSessionsController extends Controller
{
    public function __construct(
        private readonly RevokeOtherSessionsUseCase $revokeOtherSessionsUseCase,
        private readonly Translator $translator
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => $this->translator->translate('messages.session.store_unavailable'),
            ], 422);
        }

        $user = $this->requireUser($request);
        $userId = $this->requireUserId($user);
        $currentSessionId = $request->session()->getId();

        $revoked = $this->revokeOtherSessionsUseCase->execute(new RevokeOtherSessionsUseCaseRequest(
            userId: $userId,
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
