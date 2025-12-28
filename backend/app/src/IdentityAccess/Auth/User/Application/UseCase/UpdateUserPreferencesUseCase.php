<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\GetUserPreferencesUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use InvalidArgumentException;

final class UpdateUserPreferencesUseCase
{
    public function __construct(
        private readonly UserPreferencesRepository $preferences,
        private readonly GetUserPreferencesUseCase $getUserPreferencesUseCase
    ) {
    }

    public function execute(UpdateUserPreferencesUseCaseRequest $request): GetUserPreferencesUseCaseResponse
    {
        $userId = new UserId($request->userId);

        $preferences = $this->preferences->find($userId)
            ?? UserPreferences::default($userId);

        if ($request->locale !== null) {
            $this->assertLocaleSupported($request->locale);
            $preferences->updateLocale($request->locale);
        }

        if ($request->theme !== null) {
            $this->assertThemeSupported($request->theme);
            $preferences->updateTheme($request->theme);
        }

        $this->preferences->save($preferences);

        return $this->getUserPreferencesUseCase->execute(
            new GetUserPreferencesUseCaseRequest(
                userId: $request->userId
            )
        );
    }

    private function assertLocaleSupported(string $locale): void
    {
        $supported = array_keys((array) config('app.supported_locales', []));

        if (! in_array($locale, $supported, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported locale <%s>', $locale));
        }
    }

    private function assertThemeSupported(string $theme): void
    {
        $themes = array_keys((array) config('preferences.themes', []));

        if (! in_array($theme, $themes, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported theme <%s>', $theme));
        }
    }
}
