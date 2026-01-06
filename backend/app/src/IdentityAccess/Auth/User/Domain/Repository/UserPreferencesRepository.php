<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Repository;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

interface UserPreferencesRepository
{
    public function find(UserId $userId): ?UserPreferences;

    public function save(UserPreferences $preferences): void;
}
