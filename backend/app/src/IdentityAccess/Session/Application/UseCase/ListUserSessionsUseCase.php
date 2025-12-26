<?php

namespace App\Src\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Request\ListUserSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;

final class ListUserSessionsUseCase
{
    public function __construct(
        private readonly SessionRepository $sessions
    ) {
    }

    /**
     * @return array<int, array{id:string,ip_address:?string,user_agent:?string,last_activity:int,is_current:bool}>
     */
    public function execute(ListUserSessionsUseCaseRequest $request): array
    {
        return array_values(array_map(function (array $row) use ($request) {
            return (new SessionInfo(
                id: $row['id'],
                ipAddress: $row['ip_address'],
                userAgent: $row['user_agent'],
                lastActivity: $row['last_activity'],
                isCurrent: $row['id'] === $request->currentSessionId,
            ))->toArray();
        }, $this->sessions->listForUser($request->userId)));
    }
}




