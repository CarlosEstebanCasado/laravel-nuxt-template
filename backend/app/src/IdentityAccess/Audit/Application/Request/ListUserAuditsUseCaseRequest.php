<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Application\Request;

final class ListUserAuditsUseCaseRequest
{
    public function __construct(
        public readonly string $auditableType,
        public readonly int $auditableId,
        public readonly int $perPage,
        public readonly int $page,
    ) {
    }
}




