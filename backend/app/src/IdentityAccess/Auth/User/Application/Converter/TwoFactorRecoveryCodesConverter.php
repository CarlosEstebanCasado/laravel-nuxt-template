<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Converter;

use App\Src\IdentityAccess\Auth\User\Application\Response\TwoFactorRecoveryCodesUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Collection\RecoveryCodeCollection;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;

final class TwoFactorRecoveryCodesConverter
{
    public function __construct(
        private readonly TwoFactorRecoveryCodeConverter $twoFactorRecoveryCodeConverter
    ) {
    }

    public function toResponse(RecoveryCodeCollection $recoveryCodeCollection): TwoFactorRecoveryCodesUseCaseResponse
    {
        $codes = [];

        /** @var RecoveryCode $code */
        foreach ($recoveryCodeCollection as $code) {
            $codes[] = $this->twoFactorRecoveryCodeConverter->toResponse($code);
        }

        return new TwoFactorRecoveryCodesUseCaseResponse($codes);
    }
}
