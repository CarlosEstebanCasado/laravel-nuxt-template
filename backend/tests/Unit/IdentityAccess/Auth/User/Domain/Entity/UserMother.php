<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject\AuthProviderMother;
use Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddressMother;
use Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject\UserIdMother;
use Tests\Unit\Shared\Mother\DateTimeMother;
use Tests\Unit\Shared\Mother\WordMother;

final class UserMother
{
    public static function withPasswordProvider(): User
    {
        return new User(
            id: UserIdMother::random(),
            name: new UserName(WordMother::random()),
            email: EmailAddressMother::random(),
            emailVerifiedAt: new DateTimeValue(DateTimeMother::now()),
            authProvider: AuthProviderMother::password(),
            passwordSetAt: new DateTimeValue(DateTimeMother::now()),
            createdAt: new DateTimeValue(DateTimeMother::now()),
            updatedAt: new DateTimeValue(DateTimeMother::now()),
        );
    }
}
