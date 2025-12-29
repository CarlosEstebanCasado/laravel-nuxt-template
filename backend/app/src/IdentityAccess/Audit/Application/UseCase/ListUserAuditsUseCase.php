<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Application\UseCase;

use App\Src\IdentityAccess\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\Src\IdentityAccess\Audit\Application\Response\GetAuditListUseCaseResponse;
use App\Src\IdentityAccess\Audit\Application\Converter\AuditListConverter;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;

final class ListUserAuditsUseCase
{
    public function __construct(
        private readonly AuditRepository $auditRepository,
        private readonly AuditListConverter $auditListConverter,
    ) {
    }

    public function execute(ListUserAuditsUseCaseRequest $request): GetAuditListUseCaseResponse
    {
        $result = $this->auditRepository->paginateForAuditable(
            auditableType: $request->auditableType,
            auditableId: $request->auditableId,
            perPage: $request->perPage,
            page: $request->page,
        );

        return $this->auditListConverter->toResponse($result);
    }
}
