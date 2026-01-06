<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify;

use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorAuthenticationService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

final class FortifyTwoFactorAuthenticationService implements TwoFactorAuthenticationService
{
    public function __construct(
        private readonly DisableTwoFactorAuthentication $disableTwoFactorAuthentication
    ) {}

    public function disableForUser(UserId $userId): void
    {
        $user = User::query()->find($userId->toInt());

        if (! $user) {
            throw new ModelNotFoundException('User not found for disabling two-factor authentication.');
        }

        ($this->disableTwoFactorAuthentication)($user);
    }
}
