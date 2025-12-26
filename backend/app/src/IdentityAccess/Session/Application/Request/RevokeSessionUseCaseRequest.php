<?php

namespace App\Src\IdentityAccess\Session\Application\Request;

final class RevokeSessionUseCaseRequest
{
    /**
     * @param  array<string, mixed>  $auditNewValues
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $sessionId,
        public readonly string $currentSessionId,
        public readonly ?string $url,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly array $auditNewValues = [],
    ) {
    }
}




