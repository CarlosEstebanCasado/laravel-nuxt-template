<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPreferencesUseCase;
use App\Src\Shared\Domain\Service\ConfigProvider;
use App\Src\Shared\UI\Controllers\Controller;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class UpdateUserPreferencesController extends Controller
{
    public function __construct(
        private readonly UpdateUserPreferencesUseCase $updateUserPreferencesUseCase,
        private readonly GetUserPreferencesUseCase $getUserPreferencesUseCase,
        private readonly ConfigProvider $configProvider
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $supportedLocales = array_keys((array) $this->configProvider->get('app.supported_locales', []));
        $supportedThemes = array_keys((array) $this->configProvider->get('preferences.themes', []));
        $supportedPrimary = array_keys((array) $this->configProvider->get('preferences.primary_colors', []));
        $supportedNeutrals = array_keys((array) $this->configProvider->get('preferences.neutral_colors', []));
        $supportedTimezones = DateTimeZone::listIdentifiers();

        $validated = $request->validate([
            'locale' => [
                'sometimes',
                'string',
                Rule::in($supportedLocales),
            ],
            'theme' => [
                'sometimes',
                'string',
                Rule::in($supportedThemes),
            ],
            'primary_color' => [
                'sometimes',
                'string',
                Rule::in($supportedPrimary),
            ],
            'neutral_color' => [
                'sometimes',
                'string',
                Rule::in($supportedNeutrals),
            ],
            'timezone' => [
                'sometimes',
                'string',
                Rule::in($supportedTimezones),
            ],
        ]);

        if ($validated === []) {
            $result = $this->getUserPreferencesUseCase->execute(
                new GetUserPreferencesUseCaseRequest(
                    userId: $this->requireUserId($user)
                )
            );

            return response()->json($result);
        }

        $result = $this->updateUserPreferencesUseCase->execute(
            new UpdateUserPreferencesUseCaseRequest(
                userId: $this->requireUserId($user),
                locale: $validated['locale'] ?? null,
                theme: $validated['theme'] ?? null,
                primaryColor: $validated['primary_color'] ?? null,
                neutralColor: $validated['neutral_color'] ?? null,
                timezone: $validated['timezone'] ?? null,
            )
        );

        return response()->json($result);
    }
}
