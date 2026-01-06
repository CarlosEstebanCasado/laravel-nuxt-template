<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Audit\Domain\Entity;

use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditEvent;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditId;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditTags;
use App\Src\IdentityAccess\Audit\Domain\ValueObject\AuditValues;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;
use Tests\Unit\Shared\Mother\ArrayMother;
use Tests\Unit\Shared\Mother\DateTimeMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class AuditMother
{
    public static function random(): Audit
    {
        return new Audit(
            id: new AuditId(IntegerMother::random()),
            event: new AuditEvent(WordMother::random()),
            createdAt: new DateTimeValue(DateTimeMother::now()),
            oldValues: new AuditValues(ArrayMother::associative()),
            newValues: new AuditValues(ArrayMother::associative()),
            ipAddress: new IpAddress(IpMother::random()),
            userAgent: new UserAgent(UserAgentMother::random()),
            tags: new AuditTags(WordMother::random()),
        );
    }
}
