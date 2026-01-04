<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Converter\TwoFactorRecoveryCodesConverter;
use App\Src\IdentityAccess\Auth\User\Application\Request\RegenerateTwoFactorRecoveryCodesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\TwoFactorRecoveryCodesUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorRecoveryCodesService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class RegenerateTwoFactorRecoveryCodesUseCase
{
    public function __construct(
        private readonly TwoFactorRecoveryCodesService $twoFactorRecoveryCodesService,
        private readonly TwoFactorRecoveryCodesConverter $twoFactorRecoveryCodesConverter
    ) {
    }

    public function execute(RegenerateTwoFactorRecoveryCodesUseCaseRequest $request): TwoFactorRecoveryCodesUseCaseResponse
    {
        $collection = $this->twoFactorRecoveryCodesService->regenerateForUser(
            new UserId($request->userId)
        );

        return $this->twoFactorRecoveryCodesConverter->toResponse($collection);
    }
}
