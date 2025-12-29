<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Domain\Entity;

use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionCurrent;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionId;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionLastActivity;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class SessionInfoMother
{
    public static function random(bool $isCurrent = false): SessionInfo
    {
        return new SessionInfo(
            id: new SessionId(WordMother::random()),
            ipAddress: new IpAddress(IpMother::random()),
            userAgent: new UserAgent(UserAgentMother::random()),
            lastActivity: new SessionLastActivity(IntegerMother::random()),
            isCurrent: new SessionCurrent($isCurrent),
        );
    }
}
