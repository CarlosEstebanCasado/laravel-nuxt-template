<?php

namespace App\Src\Session\Session\Application\UseCase;

use App\Src\Session\Session\Application\Request\RevokeOtherSessionsUseCaseRequest;
use App\Src\Session\Session\Domain\Repository\SessionRepository;
use App\Src\Shared\Shared\Domain\Service\AuditEventRecorder;

final class RevokeOtherSessionsUseCase
{
    public function __construct(
        private readonly SessionRepository $sessions,
        private readonly AuditEventRecorder $audit
    ) {
    }

    public function execute(RevokeOtherSessionsUseCaseRequest $request): int
    {
        $revoked = $this->sessions->deleteOthersForUser($request->userId, $request->currentSessionId);

        $this->audit->recordUserEvent(
            userId: $request->userId,
            event: 'sessions_revoked',
            newValues: $request->auditNewValues ?: ['revoked' => $revoked],
            url: $request->url,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            tags: 'security',
        );

        return $revoked;
    }
}




