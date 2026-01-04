<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\DisableTwoFactorAuthenticationUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\DisableTwoFactorAuthenticationUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DisableTwoFactorAuthenticationController extends Controller
{
    public function __construct(
        private readonly DisableTwoFactorAuthenticationUseCase $disableTwoFactorAuthenticationUseCase
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->requireUser($request);

        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
        ]);

        $this->disableTwoFactorAuthenticationUseCase->execute(
            new DisableTwoFactorAuthenticationUseCaseRequest(
                userId: $this->requireUserId($user),
            )
        );

        return response()->noContent();
    }
}
