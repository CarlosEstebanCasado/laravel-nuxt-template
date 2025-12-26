<?php

namespace App\BoundedContext\Security\Reauth\Application\UseCase;

use App\BoundedContext\Security\Reauth\Application\Request\DeleteAccountUseCaseRequest;
use App\BoundedContext\Security\Reauth\Domain\Repository\AccountRepository;
use App\BoundedContext\Shared\Shared\Domain\Service\AuditEventRecorder;

final class DeleteAccountUseCase
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly AuditEventRecorder $audit
    ) {
    }

    public function execute(DeleteAccountUseCaseRequest $request): void
    {
        $this->audit->recordUserEvent(
            userId: $request->userId,
            event: 'account_deleted',
            newValues: ['confirmation' => $request->confirmation],
            url: $request->url,
            ipAddress: $request->ipAddress,
            userAgent: $request->userAgent,
            tags: 'security',
        );

        $this->accounts->deleteAccount($request->userId);
    }
}




