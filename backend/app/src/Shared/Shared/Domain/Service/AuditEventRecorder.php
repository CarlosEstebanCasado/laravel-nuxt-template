<?php

namespace App\BoundedContext\Shared\Shared\Domain\Service;

interface AuditEventRecorder
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function recordUserEvent(
        int $userId,
        string $event,
        array $oldValues = [],
        array $newValues = [],
        ?string $url = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $tags = 'security',
    ): void;
}




