<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Service;

use App\Src\IdentityAccess\Auth\User\Domain\Collection\RecoveryCodeCollection;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

interface TwoFactorRecoveryCodesService
{
    public function getForUser(UserId $userId): RecoveryCodeCollection;

    public function regenerateForUser(UserId $userId): RecoveryCodeCollection;
}
