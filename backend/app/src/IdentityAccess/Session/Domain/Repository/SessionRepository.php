<?php

namespace App\Src\IdentityAccess\Session\Domain\Repository;

use App\Src\IdentityAccess\Session\Domain\Response\SessionCollectionResponse;

interface SessionRepository
{
    public function listForUser(int $userId): SessionCollectionResponse;

    public function deleteForUser(string $sessionId, int $userId): int;

    public function deleteOthersForUser(int $userId, string $currentSessionId): int;
}



