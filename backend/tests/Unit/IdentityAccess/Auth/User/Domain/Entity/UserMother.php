<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;
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
            name: WordMother::random(),
            email: EmailAddressMother::random(),
            emailVerifiedAt: DateTimeMother::now(),
            authProvider: AuthProviderMother::password(),
            passwordSetAt: DateTimeMother::now(),
            createdAt: DateTimeMother::now(),
            updatedAt: DateTimeMother::now(),
        );
    }
}
