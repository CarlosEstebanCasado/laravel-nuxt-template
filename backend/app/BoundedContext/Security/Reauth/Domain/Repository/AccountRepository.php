<?php

namespace App\BoundedContext\Security\Reauth\Domain\Repository;

interface AccountRepository
{
    public function deleteAccount(int $userId): void;
}




