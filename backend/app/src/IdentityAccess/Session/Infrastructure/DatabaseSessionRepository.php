<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Infrastructure;

use App\Src\IdentityAccess\Session\Domain\Collection\SessionCollection;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;
use Illuminate\Support\Facades\DB;

final class DatabaseSessionRepository implements SessionRepository
{
    public function listForUser(int $userId): SessionCollectionResponse
    {
        $items = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get([
                'id',
                'ip_address',
                'user_agent',
                'last_activity',
            ])
            ->map(function ($row) {
                return new SessionInfo(
                    id: (string) $row->id,
                    ipAddress: $row->ip_address,
                    userAgent: $row->user_agent,
                    lastActivity: (int) $row->last_activity,
                    isCurrent: false,
                );
            })
            ->values()
            ->all();

        return new SessionCollectionResponse(
            new SessionCollection($items)
        );
    }

    public function deleteForUser(string $sessionId, int $userId): int
    {
        return DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function deleteOthersForUser(int $userId, string $currentSessionId): int
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}


