<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify;

use App\Src\IdentityAccess\Auth\User\Domain\Collection\RecoveryCodeCollection;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorRecoveryCodesService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Encryption\DecryptException;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Fortify;

final class FortifyTwoFactorRecoveryCodesService implements TwoFactorRecoveryCodesService
{
    public function __construct(
        private readonly GenerateNewRecoveryCodes $generateNewRecoveryCodes
    ) {
    }

    public function getForUser(UserId $userId): RecoveryCodeCollection
    {
        $user = $this->requireUser($userId);

        return $this->parseRecoveryCodes($user);
    }

    public function regenerateForUser(UserId $userId): RecoveryCodeCollection
    {
        $user = $this->requireUser($userId);

        ($this->generateNewRecoveryCodes)($user);
        $user->refresh();

        return $this->parseRecoveryCodes($user);
    }

    private function requireUser(UserId $userId): User
    {
        $user = User::query()->find($userId->toInt());

        if (! $user) {
            throw new ModelNotFoundException('User not found for recovery codes.');
        }

        return $user;
    }

    private function parseRecoveryCodes(User $user): RecoveryCodeCollection
    {
        if (! $user->two_factor_secret || ! $user->two_factor_recovery_codes) {
            return new RecoveryCodeCollection([]);
        }

        $codes = $this->normalizeRecoveryCodes($user->two_factor_recovery_codes);

        $items = array_map(
            static fn (string $code) => new RecoveryCode($code),
            is_array($codes) ? $codes : []
        );

        return new RecoveryCodeCollection($items);
    }

    private function normalizeRecoveryCodes(string|array $rawCodes): array
    {
        if (is_array($rawCodes)) {
            return $rawCodes;
        }

        $decoded = json_decode($rawCodes, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        try {
            $decoded = json_decode(Fortify::currentEncrypter()->decrypt($rawCodes), true);
        } catch (DecryptException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
