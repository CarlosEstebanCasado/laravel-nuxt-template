<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Domain\Service;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use InvalidArgumentException;

final class UserPreferencesUpdater
{
    public function update(
        UserPreferences $preferences,
        ?Locale $locale,
        ?Theme $theme,
        ?PrimaryColor $primaryColor,
        ?NeutralColor $neutralColor
    ): UserPreferences {
        $nextLocale = $locale ?? $preferences->locale();
        $nextTheme = $theme ?? $preferences->theme();
        $nextPrimary = $primaryColor ?? $preferences->primaryColor();
        $nextNeutral = $neutralColor ?? $preferences->neutralColor();

        $this->assertLocaleSupported($nextLocale);
        $this->assertThemeSupported($nextTheme);
        $this->assertPrimaryColorSupported($nextPrimary);
        $this->assertNeutralColorSupported($nextNeutral);

        return new UserPreferences(
            $preferences->userId(),
            $nextLocale,
            $nextTheme,
            $nextPrimary,
            $nextNeutral
        );
    }

    private function assertLocaleSupported(Locale $locale): void
    {
        $supported = array_keys((array) config('app.supported_locales', []));

        if (! in_array($locale->toString(), $supported, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported locale <%s>', $locale->toString()));
        }
    }

    private function assertThemeSupported(Theme $theme): void
    {
        $themes = array_keys((array) config('preferences.themes', []));

        if (! in_array($theme->toString(), $themes, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported theme <%s>', $theme->toString()));
        }
    }

    private function assertPrimaryColorSupported(PrimaryColor $color): void
    {
        $colors = array_keys((array) config('preferences.primary_colors', []));

        if (! in_array($color->toString(), $colors, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported primary color <%s>', $color->toString()));
        }
    }

    private function assertNeutralColorSupported(NeutralColor $color): void
    {
        $colors = array_keys((array) config('preferences.neutral_colors', []));

        if (! in_array($color->toString(), $colors, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported neutral color <%s>', $color->toString()));
        }
    }
}
