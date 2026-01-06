<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Session\Domain\Response;

use App\Src\IdentityAccess\Session\Domain\Collection\SessionCollection;

final class SessionCollectionResponse
{
    public function __construct(
        private readonly SessionCollection $items
    ) {}

    public function items(): SessionCollection
    {
        return $this->items;
    }
}
