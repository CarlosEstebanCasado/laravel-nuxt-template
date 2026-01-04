<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

final class TwoFactorRecoveryCodesUseCaseResponse
{
    /**
     * @param string[] $codes
     */
    public function __construct(
        public array $codes
    ) {
    }
}
