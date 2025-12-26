<?php

namespace App\Src\Audit\Audit\Application\Request;

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




