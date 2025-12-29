<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\Converter;

use App\Src\IdentityAccess\Session\Application\Response\SessionResponseItem;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionId;

final class SessionResponseItemConverter
{
    public function toResponse(SessionInfo $session, SessionId $currentSessionId): SessionResponseItem
    {
        $ipAddress = $session->ipAddress();
        $userAgent = $session->userAgent();
        $isCurrent = $session->isCurrent()->value() || $session->id()->equals($currentSessionId);

        return new SessionResponseItem(
            id: $session->id()->toString(),
            ip_address: $ipAddress?->toString(),
            user_agent: $userAgent?->toString(),
            last_activity: $session->lastActivity()->toInt(),
            is_current: $isCurrent,
        );
    }
}
