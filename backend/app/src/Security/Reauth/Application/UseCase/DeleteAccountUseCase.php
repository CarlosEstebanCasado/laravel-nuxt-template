<?php

namespace App\Src\Security\Reauth\Application\UseCase;

use App\Src\Security\Reauth\Application\Request\DeleteAccountUseCaseRequest;
use App\Src\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\Shared\Shared\Domain\Service\AuditEventRecorder;

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




