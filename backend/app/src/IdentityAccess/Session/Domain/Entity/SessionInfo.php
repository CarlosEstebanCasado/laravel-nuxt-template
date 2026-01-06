<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\Entity;

use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionCurrent;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionId;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionLastActivity;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;

final class SessionInfo
{
    public function __construct(
        private readonly SessionId $id,
        private readonly ?IpAddress $ipAddress,
        private readonly ?UserAgent $userAgent,
        private readonly SessionLastActivity $lastActivity,
        private readonly SessionCurrent $isCurrent,
    ) {}

    public function id(): SessionId
    {
        return $this->id;
    }

    public function ipAddress(): ?IpAddress
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?UserAgent
    {
        return $this->userAgent;
    }

    public function lastActivity(): SessionLastActivity
    {
        return $this->lastActivity;
    }

    public function isCurrent(): SessionCurrent
    {
        return $this->isCurrent;
    }
}
