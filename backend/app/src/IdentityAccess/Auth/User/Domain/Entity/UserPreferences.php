<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class UserPreferences
{
    public function __construct(
        private readonly UserId $userId,
        private readonly Locale $locale,
        private readonly Theme $theme,
        private readonly PrimaryColor $primaryColor,
        private readonly NeutralColor $neutralColor,
        private readonly Timezone $timezone,
    ) {
    }

    public static function create(
        UserId $userId,
        Locale $locale,
        Theme $theme,
        PrimaryColor $primaryColor,
        NeutralColor $neutralColor,
        Timezone $timezone
    ): self {
        return new self($userId, $locale, $theme, $primaryColor, $neutralColor, $timezone);
    }

    public static function default(UserId $userId): self
    {
        $configLocale = config('app.locale');
        $defaultLocale = is_string($configLocale) && $configLocale !== ''
            ? $configLocale
            : 'es';

        $configTheme = config('preferences.default_theme');
        $defaultTheme = is_string($configTheme) && $configTheme !== ''
            ? $configTheme
            : 'system';

        $defaultPrimary = self::configString('preferences.default_primary_color', 'blue');
        $defaultNeutral = self::configString('preferences.default_neutral_color', 'slate');
        $defaultTimezone = self::configString('preferences.default_timezone', 'UTC');

        return new self(
            $userId,
            new Locale($defaultLocale),
            new Theme($defaultTheme),
            new PrimaryColor($defaultPrimary),
            new NeutralColor($defaultNeutral),
            new Timezone($defaultTimezone)
        );
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function locale(): Locale
    {
        return $this->locale;
    }

    public function theme(): Theme
    {
        return $this->theme;
    }

    public function primaryColor(): PrimaryColor
    {
        return $this->primaryColor;
    }

    public function neutralColor(): NeutralColor
    {
        return $this->neutralColor;
    }

    public function timezone(): Timezone
    {
        return $this->timezone;
    }

    private static function configString(string $key, string $fallback): string
    {
        $value = config($key, $fallback);

        return is_string($value) && $value !== ''
            ? $value
            : $fallback;
    }
}
