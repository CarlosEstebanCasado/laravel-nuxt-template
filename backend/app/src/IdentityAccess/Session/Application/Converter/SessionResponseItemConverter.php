<?php

namespace App\Src\IdentityAccess\Session\Application\Converter;

use App\Src\IdentityAccess\Session\Application\Response\SessionResponseItem;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;

final class SessionResponseItemConverter
{
    public function toResponse(SessionInfo $session, string $currentSessionId): SessionResponseItem
    {
        return new SessionResponseItem(
            id: $session->id,
            ip_address: $session->ipAddress,
            user_agent: $session->userAgent,
            last_activity: $session->lastActivity,
            is_current: $session->id === $currentSessionId,
        );
    }
}

