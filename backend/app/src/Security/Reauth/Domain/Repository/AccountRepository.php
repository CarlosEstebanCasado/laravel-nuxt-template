<?php

namespace App\Src\Security\Reauth\Domain\Repository;

interface AccountRepository
{
    public function deleteAccount(int $userId): void;
}




