<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Security\Reauth\Domain\Repository;

interface AccountRepository
{
    public function deleteAccount(int $userId): void;
}
