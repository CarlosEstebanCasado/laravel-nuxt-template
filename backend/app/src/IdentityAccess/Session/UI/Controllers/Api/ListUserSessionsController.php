<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\UI\Controllers\Api;

use App\Src\IdentityAccess\Session\Application\Request\ListUserSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\ListUserSessionsUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListUserSessionsController extends Controller
{
    public function __construct(
        private readonly ListUserSessionsUseCase $listUserSessionsUseCase
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->hasSession()) {
            return response()->json([
                'message' => 'Session store is not available for this request.',
            ], 422);
        }

        $user = $this->requireUser($request);
        $userId = $this->requireUserId($user);
        $currentSessionId = $request->session()->getId();

        $sessions = $this->listUserSessionsUseCase->execute(new ListUserSessionsUseCaseRequest(
            userId: $userId,
            currentSessionId: (string) $currentSessionId,
        ));

        return response()->json($sessions);
    }
}
