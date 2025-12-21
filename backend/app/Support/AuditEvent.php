<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditEvent
{
    /**
     * Record a manual audit event for the given user.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function record(
        User $user,
        string $event,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null,
        ?string $tags = 'security'
    ): Audit {
        $request ??= request();

        return Audit::query()->create([
            'user_type' => User::class,
            'user_id' => $user->getAuthIdentifier(),
            'event' => $event,
            'auditable_type' => User::class,
            'auditable_id' => $user->getAuthIdentifier(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'tags' => $tags,
        ]);
    }
}


