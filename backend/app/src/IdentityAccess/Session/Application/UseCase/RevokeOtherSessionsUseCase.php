<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Request\RevokeOtherSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;

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




