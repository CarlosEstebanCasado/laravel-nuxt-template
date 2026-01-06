<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\Service;

use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionCurrent;

final class SessionInfoCurrentMarker
{
    public function mark(SessionInfo $sessionInfo): SessionInfo
    {
        return new SessionInfo(
            id: $sessionInfo->id(),
            ipAddress: $sessionInfo->ipAddress(),
            userAgent: $sessionInfo->userAgent(),
            lastActivity: $sessionInfo->lastActivity(),
            isCurrent: new SessionCurrent(true),
        );
    }
}
