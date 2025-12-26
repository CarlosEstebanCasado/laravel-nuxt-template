<?php

namespace App\Src\Session\Session\Infrastructure;

use App\Src\Session\Session\Domain\Repository\SessionRepository;
use Illuminate\Support\Facades\DB;

final class DatabaseSessionRepository implements SessionRepository
{
    public function listForUser(int $userId): array
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get([
                'id',
                'ip_address',
                'user_agent',
                'last_activity',
            ])
            ->map(function ($row) {
                return [
                    'id' => (string) $row->id,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => (int) $row->last_activity,
                ];
            })
            ->values()
            ->all();
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




