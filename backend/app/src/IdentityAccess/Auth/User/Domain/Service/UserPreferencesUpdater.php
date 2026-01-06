<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Service;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\Shared\Domain\Service\ConfigProvider;
use DateTimeZone;
use InvalidArgumentException;

final class UserPreferencesUpdater
{
    public function __construct(
        private readonly ConfigProvider $configProvider
    ) {}

    public function update(
        UserPreferences $preferences,
        ?Locale $locale,
        ?Theme $theme,
        ?PrimaryColor $primaryColor,
        ?NeutralColor $neutralColor,
        ?Timezone $timezone
    ): UserPreferences {
        $nextLocale = $locale ?? $preferences->locale();
        $nextTheme = $theme ?? $preferences->theme();
        $nextPrimary = $primaryColor ?? $preferences->primaryColor();
        $nextNeutral = $neutralColor ?? $preferences->neutralColor();
        $nextTimezone = $timezone ?? $preferences->timezone();

        if ($locale !== null) {
            $this->assertLocaleSupported($nextLocale);
        }

        if ($theme !== null) {
            $this->assertThemeSupported($nextTheme);
        }

        if ($primaryColor !== null) {
            $this->assertPrimaryColorSupported($nextPrimary);
        }

        if ($neutralColor !== null) {
            $this->assertNeutralColorSupported($nextNeutral);
        }

        if ($timezone !== null) {
            $this->assertTimezoneSupported($nextTimezone);
        }

        return new UserPreferences(
            $preferences->userId(),
            $nextLocale,
            $nextTheme,
            $nextPrimary,
            $nextNeutral,
            $nextTimezone
        );
    }

    private function assertLocaleSupported(Locale $locale): void
    {
        $supported = array_keys((array) $this->configProvider->get('app.supported_locales', []));

        if (! in_array($locale->toString(), $supported, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported locale <%s>', $locale->toString()));
        }
    }

    private function assertThemeSupported(Theme $theme): void
    {
        $themes = array_keys((array) $this->configProvider->get('preferences.themes', []));

        if (! in_array($theme->toString(), $themes, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported theme <%s>', $theme->toString()));
        }
    }

    private function assertPrimaryColorSupported(PrimaryColor $color): void
    {
        $colors = array_keys((array) $this->configProvider->get('preferences.primary_colors', []));

        if (! in_array($color->toString(), $colors, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported primary color <%s>', $color->toString()));
        }
    }

    private function assertNeutralColorSupported(NeutralColor $color): void
    {
        $colors = array_keys((array) $this->configProvider->get('preferences.neutral_colors', []));

        if (! in_array($color->toString(), $colors, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported neutral color <%s>', $color->toString()));
        }
    }

    private function assertTimezoneSupported(Timezone $timezone): void
    {
        $timezones = DateTimeZone::listIdentifiers();

        if (! in_array($timezone->toString(), $timezones, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported timezone <%s>', $timezone->toString()));
        }
    }
}
