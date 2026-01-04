<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Repository;

use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\UserPreference;
use InvalidArgumentException;

final class EloquentUserPreferencesRepository implements UserPreferencesRepository
{
    public function find(UserId $userId): ?UserPreferences
    {
        /** @var UserPreference|null $model */
        $model = UserPreference::query()
            ->where('user_id', $userId->toInt())
            ->first();

        if (! $model) {
            return null;
        }

        return $this->mapToDomain($model);
    }

    public function save(UserPreferences $preferences): void
    {
        /** @var UserPreference $model */
        $model = UserPreference::query()->firstOrNew([
            'user_id' => $preferences->userId()->toInt(),
        ]);

        $model->setAttribute('locale', $preferences->locale()->toString());
        $model->setAttribute('theme', $preferences->theme()->toString());
        $model->setAttribute('primary_color', $preferences->primaryColor()->toString());
        $model->setAttribute('neutral_color', $preferences->neutralColor()->toString());
        $model->setAttribute('timezone', $preferences->timezone()->toString());
        $model->save();
    }

    private function mapToDomain(UserPreference $model): UserPreferences
    {
        $userIdValue = $model->getAttribute('user_id');
        $localeValue = $model->getAttribute('locale');
        $themeValue = $model->getAttribute('theme');
        $primaryColorValue = $model->getAttribute('primary_color');
        $neutralColorValue = $model->getAttribute('neutral_color');
        $timezoneValue = $model->getAttribute('timezone');

        $isNumericId = is_int($userIdValue)
            || (is_string($userIdValue) && ctype_digit($userIdValue));

        $fallbackLocale = config('app.locale', 'es');
        $fallbackTheme = config('preferences.default_theme', 'system');
        $fallbackPrimary = config('preferences.default_primary_color', 'blue');
        $fallbackNeutral = config('preferences.default_neutral_color', 'slate');
        $fallbackTimezone = config('preferences.default_timezone', 'UTC');

        $locale = is_string($localeValue) ? $localeValue : (is_string($fallbackLocale) ? $fallbackLocale : 'es');
        $theme = is_string($themeValue) ? $themeValue : (is_string($fallbackTheme) ? $fallbackTheme : 'system');
        $primaryColor = is_string($primaryColorValue)
            ? $primaryColorValue
            : (is_string($fallbackPrimary) ? $fallbackPrimary : 'blue');
        $neutralColor = is_string($neutralColorValue)
            ? $neutralColorValue
            : (is_string($fallbackNeutral) ? $fallbackNeutral : 'slate');
        $timezone = is_string($timezoneValue) ? $timezoneValue : (is_string($fallbackTimezone) ? $fallbackTimezone : 'UTC');

        if (! $isNumericId) {
            throw new InvalidArgumentException('User preferences user_id must be numeric.');
        }

        $userId = new UserId((int) $userIdValue);

        return new UserPreferences(
            $userId,
            new Locale($locale),
            new Theme($theme),
            new PrimaryColor($primaryColor),
            new NeutralColor($neutralColor),
            new Timezone($timezone)
        );
    }
}
