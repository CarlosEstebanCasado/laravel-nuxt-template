<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\RegenerateTwoFactorRecoveryCodesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\RegenerateTwoFactorRecoveryCodesUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegenerateTwoFactorRecoveryCodesController extends Controller
{
    public function __construct(
        private readonly RegenerateTwoFactorRecoveryCodesUseCase $regenerateTwoFactorRecoveryCodesUseCase
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
        ]);

        $response = $this->regenerateTwoFactorRecoveryCodesUseCase->execute(
            new RegenerateTwoFactorRecoveryCodesUseCaseRequest(
                userId: $this->requireUserId($user),
            )
        );

        return response()->json($response);
    }
}
