<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use Tests\Unit\Shared\Mother\WordMother;

final class AuthProviderMother
{
    public static function password(): AuthProvider
    {
        return new AuthProvider('password');
    }

    public static function random(): AuthProvider
    {
        return new AuthProvider(WordMother::random());
    }
}
