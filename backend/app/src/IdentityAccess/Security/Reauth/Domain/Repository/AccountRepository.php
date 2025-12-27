<?php

namespace App\Src\IdentityAccess\Security\Reauth\Domain\Repository;

interface AccountRepository
{
    public function deleteAccount(int $userId): void;
}




