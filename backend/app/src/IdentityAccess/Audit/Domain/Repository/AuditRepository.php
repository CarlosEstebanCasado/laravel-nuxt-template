<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Repository;

use App\Src\IdentityAccess\Audit\Domain\Response\AuditCollectionResponse;

interface AuditRepository
{
    public function paginateForAuditable(
        string $auditableType,
        int $auditableId,
        int $perPage,
        int $page
    ): AuditCollectionResponse;
}



