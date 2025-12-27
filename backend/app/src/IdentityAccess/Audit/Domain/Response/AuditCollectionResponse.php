<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Response;

use App\Src\IdentityAccess\Audit\Domain\Collection\AuditCollection;
use App\Src\Shared\Domain\Response\PaginationResponse;

final class AuditCollectionResponse
{
    public function __construct(
        private readonly AuditCollection $items,
        private readonly ?PaginationResponse $pagination = null,
    ) {
    }

    public function items(): AuditCollection
    {
        return $this->items;
    }

    public function pagination(): ?PaginationResponse
    {
        return $this->pagination;
    }
}

