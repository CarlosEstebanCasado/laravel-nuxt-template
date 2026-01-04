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

        if (! $user->two_factor_secret) {
            return new RecoveryCodeCollection([]);
        }

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

        $items = [];
        foreach ($codes as $code) {
            $items[] = new RecoveryCode($code);
        }

        return new RecoveryCodeCollection($items);
    }

    /**
     * @param array<int, string> $rawCodes
     * @return list<string>
     */
    private function normalizeRecoveryCodes(string|array $rawCodes): array
    {
        if (is_array($rawCodes)) {
            return array_values(array_filter($rawCodes, 'is_string'));
        }

        $decoded = json_decode($rawCodes, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }

        try {
            $decrypted = Fortify::currentEncrypter()->decrypt($rawCodes);
            if (! is_string($decrypted)) {
                return [];
            }

            $decoded = json_decode($decrypted, true);
        } catch (DecryptException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, 'is_string'));
    }
}
