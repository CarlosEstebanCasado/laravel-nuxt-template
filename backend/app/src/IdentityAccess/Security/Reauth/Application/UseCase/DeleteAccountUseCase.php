<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Security\Reauth\Application\UseCase;

use App\Src\IdentityAccess\Security\Reauth\Application\Request\DeleteAccountUseCaseRequest;
use App\Src\IdentityAccess\Security\Reauth\Application\Response\DeleteAccountUseCaseResponse;
use App\Src\IdentityAccess\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;

final class DeleteAccountUseCase
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly AuditEventRecorder $audit
    ) {
    }

    public function execute(DeleteAccountUseCaseRequest $request): DeleteAccountUseCaseResponse
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

        return new DeleteAccountUseCaseResponse();
    }
}


