<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject\UserIdMother;

final class UserPreferencesMother
{
    public static function random(?UserId $userId = null): UserPreferences
    {
        return new UserPreferences(
            $userId ?? UserIdMother::random(),
            new Locale('es'),
            new Theme('system'),
            new PrimaryColor('blue'),
            new NeutralColor('slate'),
            new Timezone('UTC')
        );
    }

    public static function withLocale(UserId $userId, string $locale): UserPreferences
    {
        return new UserPreferences(
            $userId,
            new Locale($locale),
            new Theme('system'),
            new PrimaryColor('blue'),
            new NeutralColor('slate'),
            new Timezone('UTC')
        );
    }

    public static function withTheme(UserId $userId, string $theme): UserPreferences
    {
        return new UserPreferences(
            $userId,
            new Locale('es'),
            new Theme($theme),
            new PrimaryColor('blue'),
            new NeutralColor('slate'),
            new Timezone('UTC')
        );
    }
}
