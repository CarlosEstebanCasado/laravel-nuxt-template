<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Entity;

use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditEvent;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditId;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditTags;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditValues;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;

final class Audit
{
    public function __construct(
        private readonly AuditId $id,
        private readonly AuditEvent $event,
        private readonly DateTimeValue $createdAt,
        private readonly ?AuditValues $oldValues,
        private readonly ?AuditValues $newValues,
        private readonly ?IpAddress $ipAddress,
        private readonly ?UserAgent $userAgent,
        private readonly ?AuditTags $tags,
    ) {
    }

    public function id(): AuditId
    {
        return $this->id;
    }

    public function event(): AuditEvent
    {
        return $this->event;
    }

    public function createdAt(): DateTimeValue
    {
        return $this->createdAt;
    }

    public function oldValues(): ?AuditValues
    {
        return $this->oldValues;
    }

    public function newValues(): ?AuditValues
    {
        return $this->newValues;
    }

    public function ipAddress(): ?IpAddress
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?UserAgent
    {
        return $this->userAgent;
    }

    public function tags(): ?AuditTags
    {
        return $this->tags;
    }
}
