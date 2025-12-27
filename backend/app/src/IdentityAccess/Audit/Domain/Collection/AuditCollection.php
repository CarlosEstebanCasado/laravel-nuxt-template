<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Collection;

use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;
use App\Src\Shared\Domain\Collection;

final class AuditCollection extends Collection
{
    public function first(): ?Audit
    {
        return $this->isNotEmpty() ? reset($this->items) : null;
    }

    protected function type(): string
    {
        return Audit::class;
    }
}

