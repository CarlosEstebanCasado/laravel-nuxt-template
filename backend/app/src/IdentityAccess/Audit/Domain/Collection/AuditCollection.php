<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Audit\Domain\Collection;

use App\Src\IdentityAccess\Audit\Domain\Entity\Audit;
use App\Src\Shared\Domain\Collection;

final class AuditCollection extends Collection
{
    protected function type(): string
    {
        return Audit::class;
    }

    public function first(): ?Audit
    {
        if ($this->isEmpty()) {
            return null;
        }

        $items = $this->items();
        $first = reset($items);

        return $first instanceof Audit ? $first : null;
    }
}
