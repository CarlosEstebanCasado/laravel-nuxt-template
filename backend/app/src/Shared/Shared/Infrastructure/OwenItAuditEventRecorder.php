<?php

namespace App\BoundedContext\Shared\Shared\Infrastructure;

use App\BoundedContext\Shared\Shared\Domain\Service\AuditEventRecorder;
use App\BoundedContext\Auth\User\Infrastructure\Eloquent\Model\User;
use OwenIt\Auditing\Models\Audit;

final class OwenItAuditEventRecorder implements AuditEventRecorder
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
        ?string $tags = 'security'
    ): void {
        Audit::query()->create([
            'user_type' => User::class,
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => User::class,
            'auditable_id' => $userId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => $url,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'tags' => $tags,
        ]);
    }
}
