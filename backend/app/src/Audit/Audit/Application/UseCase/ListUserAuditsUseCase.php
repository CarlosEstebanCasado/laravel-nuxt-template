<?php

namespace App\Src\Audit\Audit\Application\UseCase;

use App\Src\Audit\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\Src\Audit\Audit\Domain\Repository\AuditRepository;

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




