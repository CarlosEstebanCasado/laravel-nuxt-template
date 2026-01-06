<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use Tests\Unit\Shared\Mother\IntegerMother;

final class UserIdMother
{
    public static function random(): UserId
    {
        return new UserId(IntegerMother::random());
    }
}
