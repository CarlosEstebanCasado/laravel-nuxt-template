<?php

namespace App\BoundedContext\Session\Session\Application\UseCase;

use App\BoundedContext\Session\Session\Application\Request\RevokeSessionUseCaseRequest;
use App\BoundedContext\Session\Session\Domain\Exception\CannotRevokeCurrentSession;
use App\BoundedContext\Session\Session\Domain\Repository\SessionRepository;
use App\BoundedContext\Shared\Shared\Domain\Service\AuditEventRecorder;

final class RevokeSessionUseCase
{
    public function __construct(
        private readonly SessionRepository $sessions,
        private readonly AuditEventRecorder $audit
    ) {
    }

    /**
     * @return bool true when revoked, false when not found
     */
    public function execute(RevokeSessionUseCaseRequest $request): bool
    {
        if ($request->sessionId === $request->currentSessionId) {
            throw new CannotRevokeCurrentSession('You cannot revoke the current session.');
        }

        $deleted = $this->sessions->deleteForUser($request->sessionId, $request->userId);

        if ($deleted === 0) {
            return false;
        }

        $this->audit->recordUserEvent(
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




