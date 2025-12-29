<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Entity;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class UserPreferences
{
    public function __construct(
        private readonly UserId $userId,
        private string $locale,
        private string $theme,
        private string $primaryColor,
        private string $neutralColor,
    ) {
    }

    public static function create(
        UserId $userId,
        string $locale,
        string $theme,
        string $primaryColor,
        string $neutralColor
    ): self {
        return new self($userId, $locale, $theme, $primaryColor, $neutralColor);
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

        return new self(
            $userId,
            $defaultLocale,
            $defaultTheme,
            $defaultPrimary,
            $defaultNeutral
        );
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function theme(): string
    {
        return $this->theme;
    }

    public function primaryColor(): string
    {
        return $this->primaryColor;
    }

    public function neutralColor(): string
    {
        return $this->neutralColor;
    }

    public function updateLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function updateTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function updatePrimaryColor(string $color): void
    {
        $this->primaryColor = $color;
    }

    public function updateNeutralColor(string $color): void
    {
        $this->neutralColor = $color;
    }

    private static function configString(string $key, string $fallback): string
    {
        $value = config($key, $fallback);

        return is_string($value) && $value !== ''
            ? $value
            : $fallback;
    }
}
