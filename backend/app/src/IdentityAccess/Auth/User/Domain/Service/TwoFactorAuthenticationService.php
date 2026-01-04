<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Service;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

interface TwoFactorAuthenticationService
{
    public function disableForUser(UserId $userId): void;
}
