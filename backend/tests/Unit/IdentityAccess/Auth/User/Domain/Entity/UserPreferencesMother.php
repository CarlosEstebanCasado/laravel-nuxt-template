<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use Tests\Unit\IdentityAccess\Auth\User\Domain\ValueObject\UserIdMother;

final class UserPreferencesMother
{
    public static function random(?UserId $userId = null): UserPreferences
    {
        return new UserPreferences(
            $userId ?? UserIdMother::random(),
            locale: 'es',
            theme: 'system',
            primaryColor: 'blue',
            neutralColor: 'slate'
        );
    }

    public static function withLocale(UserId $userId, string $locale): UserPreferences
    {
        return new UserPreferences(
            $userId,
            $locale,
            'system',
            'blue',
            'slate'
        );
    }

    public static function withTheme(UserId $userId, string $theme): UserPreferences
    {
        return new UserPreferences(
            $userId,
            'es',
            $theme,
            'blue',
            'slate'
        );
    }
}
