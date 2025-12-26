<?php

namespace App\Src\IdentityAccess\Security\Reauth\Application\Request;

final class DeleteAccountUseCaseRequest
{
    public function __construct(
        public readonly int $userId,
        public readonly string $confirmation,
        public readonly ?string $url,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {
    }
}




