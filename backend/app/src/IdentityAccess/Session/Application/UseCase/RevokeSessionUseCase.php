<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Request\RevokeSessionUseCaseRequest;
use App\Src\IdentityAccess\Session\Domain\Exception\CannotRevokeCurrentSession;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;

final class RevokeSessionUseCase
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly AuditEventRecorder $auditEventRecorder
    ) {}

    /**
     * @return bool true when revoked, false when not found
     */
    public function execute(RevokeSessionUseCaseRequest $request): bool
    {
        if ($request->sessionId === $request->currentSessionId) {
            throw new CannotRevokeCurrentSession;
        }

        $deleted = $this->sessionRepository->deleteForUser($request->sessionId, $request->userId);

        if ($deleted === 0) {
            return false;
        }

        $this->auditEventRecorder->recordUserEvent(
            userId: $request->userId,
            event: 'session_revoked',
            newValues: $request->auditNewValues ?: ['session_id' => $request->sessionId],
            url: $request->url,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            tags: 'security',
        );

        return true;
    }
}
