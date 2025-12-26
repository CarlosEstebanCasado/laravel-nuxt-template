<?php

namespace App\Src\IdentityAccess\Audit\Domain\Repository;

interface AuditRepository
{
    /**
     * @return array{data:array<int, mixed>,meta:array<string,int>}
     */
    public function paginateForAuditable(
        string $auditableType,
        int $auditableId,
        int $perPage,
        int $page
    ): array;
}




