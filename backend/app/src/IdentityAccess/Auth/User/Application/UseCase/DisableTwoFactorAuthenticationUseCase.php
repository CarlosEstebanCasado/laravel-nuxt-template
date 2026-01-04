<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\DisableTwoFactorAuthenticationUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorAuthenticationService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class DisableTwoFactorAuthenticationUseCase
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $twoFactorAuthenticationService
    ) {
    }

    public function execute(DisableTwoFactorAuthenticationUseCaseRequest $request): void
    {
        $this->twoFactorAuthenticationService->disableForUser(
            new UserId($request->userId)
        );
    }
}
