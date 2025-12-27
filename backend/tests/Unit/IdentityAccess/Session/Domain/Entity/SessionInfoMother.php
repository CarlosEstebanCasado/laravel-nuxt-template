<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Domain\Entity;

use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class SessionInfoMother
{
    public static function random(bool $isCurrent = false): SessionInfo
    {
        return new SessionInfo(
            id: WordMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            lastActivity: IntegerMother::random(),
            isCurrent: $isCurrent,
        );
    }
}
