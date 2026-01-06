<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Converter;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;

final class TwoFactorRecoveryCodeConverter
{
    public function toResponse(RecoveryCode $recoveryCode): string
    {
        return $recoveryCode->value();
    }
}
