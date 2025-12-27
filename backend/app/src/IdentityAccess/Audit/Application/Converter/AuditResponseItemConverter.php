<?php

namespace App\Src\IdentityAccess\Audit\Application\Converter;

use App\Src\IdentityAccess\Audit\Application\Response\AuditResponseItem;
use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;

final class AuditResponseItemConverter
{
    public function toResponse(Audit $audit): AuditResponseItem
    {
        return new AuditResponseItem(
            id: $audit->id(),
            event: $audit->event(),
            created_at: $audit->createdAt()->format(\DateTimeInterface::ATOM),
            old_values: $audit->oldValues(),
            new_values: $audit->newValues(),
            ip_address: $audit->ipAddress(),
            user_agent: $audit->userAgent(),
            tags: $audit->tags(),
        );
    }
}
