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
    ) {
    }

    public static function create(UserId $userId, string $locale, string $theme): self
    {
        return new self($userId, $locale, $theme);
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

        return new self(
            $userId,
            $defaultLocale,
            $defaultTheme
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

    public function updateLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function updateTheme(string $theme): void
    {
        $this->theme = $theme;
    }
}
