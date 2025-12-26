<?php

namespace App\BoundedContext\Audit\Audit\Application\UseCase;

use App\BoundedContext\Audit\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\BoundedContext\Audit\Audit\Domain\Repository\AuditRepository;

final class ListUserAuditsUseCase
{
    public function __construct(
        private readonly AuditRepository $audits
    ) {
    }

    /**
     * @return array{data:array<int, mixed>,meta:array<string,int>}
     */
    public function execute(ListUserAuditsUseCaseRequest $request): array
    {
        return $this->audits->paginateForAuditable(
            auditableType: $request->auditableType,
            auditableId: $request->auditableId,
            perPage: $request->perPage,
            page: $request->page,
        );
    }
}




