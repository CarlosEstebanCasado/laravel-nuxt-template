<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Infrastructure;

use App\Src\IdentityAccess\Session\Domain\Collection\SessionCollection;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionCurrent;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionId;
use App\Src\IdentityAccess\Session\Domain\ValueObject\SessionLastActivity;
use App\Src\Shared\Domain\ValueObject\IpAddress;
use App\Src\Shared\Domain\ValueObject\UserAgent;
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
                $ipAddress = is_string($row->ip_address) && $row->ip_address !== ''
                    ? new IpAddress($row->ip_address)
                    : null;
                $userAgent = is_string($row->user_agent) && $row->user_agent !== ''
                    ? new UserAgent($row->user_agent)
                    : null;

                return new SessionInfo(
                    id: new SessionId((string) $row->id),
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                    lastActivity: new SessionLastActivity((int) $row->last_activity),
                    isCurrent: new SessionCurrent(false),
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
