<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\GetUserPreferencesUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class GetUserPreferencesUseCase
{
    public function __construct(
        private readonly UserPreferencesRepository $preferences
    ) {
    }

    public function execute(GetUserPreferencesUseCaseRequest $request): GetUserPreferencesUseCaseResponse
    {
        $userId = new UserId($request->userId);

        $preferences = $this->preferences->find($userId)
            ?? UserPreferences::default($userId);

        return $this->buildResponse($preferences);
    }

    private function buildResponse(UserPreferences $preferences): GetUserPreferencesUseCaseResponse
    {
        return new GetUserPreferencesUseCaseResponse(
            data: [
                'locale' => $preferences->locale(),
                'theme' => $preferences->theme(),
                'primary_color' => $preferences->primaryColor(),
                'neutral_color' => $preferences->neutralColor(),
            ],
            available_locales: $this->availableLocales(),
            available_themes: $this->availableThemes(),
            available_primary_colors: $this->availablePrimaryColors(),
            available_neutral_colors: $this->availableNeutralColors(),
        );
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function availableLocales(): array
    {
        $locales = (array) config('app.supported_locales', []);
        $options = [];

        foreach ($locales as $code => $label) {
            if (! is_string($code) || ! is_string($label)) {
                continue;
            }

            $options[] = [
                'value' => $code,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function availableThemes(): array
    {
        $themes = (array) config('preferences.themes', []);
        $options = [];

        foreach ($themes as $value => $label) {
            if (! is_string($value) || ! is_string($label)) {
                continue;
            }

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function availablePrimaryColors(): array
    {
        $colors = (array) config('preferences.primary_colors', []);
        $options = [];

        foreach ($colors as $value => $label) {
            if (! is_string($value) || ! is_string($label)) {
                continue;
            }

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function availableNeutralColors(): array
    {
        $colors = (array) config('preferences.neutral_colors', []);
        $options = [];

        foreach ($colors as $value => $label) {
            if (! is_string($value) || ! is_string($label)) {
                continue;
            }

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }
}
