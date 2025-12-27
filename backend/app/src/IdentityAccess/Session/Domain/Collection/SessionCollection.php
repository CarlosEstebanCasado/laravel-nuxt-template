<?php

namespace App\Src\IdentityAccess\Session\Domain\Collection;

use App\Src\IdentityAccess\Session\Domain\Entity\SessionInfo;
use App\Src\Shared\Domain\Collection;

final class SessionCollection extends Collection
{
    protected function type(): string
    {
        return SessionInfo::class;
    }
}

