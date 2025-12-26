<?php

namespace App\BoundedContext\Session\Session\Domain\Repository;

interface SessionRepository
{
    /**
     * @return array<int, array{id:string,ip_address:?string,user_agent:?string,last_activity:int}>
     */
    public function listForUser(int $userId): array;

    public function deleteForUser(string $sessionId, int $userId): int;

    public function deleteOthersForUser(int $userId, string $currentSessionId): int;
}




