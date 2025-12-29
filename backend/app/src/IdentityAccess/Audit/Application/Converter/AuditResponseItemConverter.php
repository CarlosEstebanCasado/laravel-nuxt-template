<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Application\Converter;

use App\Src\IdentityAccess\Audit\Application\Response\AuditResponseItem;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;

final class AuditResponseItemConverter
{
    public function toResponse(Audit $audit): AuditResponseItem
    {
        $oldValues = $audit->oldValues();
        $newValues = $audit->newValues();
        $ipAddress = $audit->ipAddress();
        $userAgent = $audit->userAgent();
        $tags = $audit->tags();

        return new AuditResponseItem(
            id: $audit->id()->toInt(),
            event: $audit->event()->toString(),
            created_at: $audit->createdAt()->value()->format(\DateTimeInterface::ATOM),
            old_values: $oldValues?->values(),
            new_values: $newValues?->values(),
            ip_address: $ipAddress?->toString(),
            user_agent: $userAgent?->toString(),
            tags: $tags?->toString(),
        );
    }
}
