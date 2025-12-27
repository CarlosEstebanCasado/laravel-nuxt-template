<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Audit\Domain\Entity;

use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;
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
            id: IntegerMother::random(),
            event: WordMother::random(),
            createdAt: DateTimeMother::now(),
            oldValues: ArrayMother::associative(),
            newValues: ArrayMother::associative(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            tags: WordMother::random(),
        );
    }
}
