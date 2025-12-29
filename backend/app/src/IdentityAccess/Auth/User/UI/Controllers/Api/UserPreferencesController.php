<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPreferencesUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class UserPreferencesController extends Controller
{
    public function __construct(
        private readonly GetUserPreferencesUseCase $getPreferences,
        private readonly UpdateUserPreferencesUseCase $updatePreferences
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $result = $this->getPreferences->execute(
            new GetUserPreferencesUseCaseRequest(
                userId: $this->requireUserId($user)
            )
        );

        return response()->json($result);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $supportedLocales = array_keys((array) config('app.supported_locales', []));
        $supportedThemes = array_keys((array) config('preferences.themes', []));
        $supportedPrimary = array_keys((array) config('preferences.primary_colors', []));
        $supportedNeutrals = array_keys((array) config('preferences.neutral_colors', []));

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
        ]);

        if ($validated === []) {
            return $this->show($request);
        }

        $result = $this->updatePreferences->execute(
            new UpdateUserPreferencesUseCaseRequest(
                userId: $this->requireUserId($user),
                locale: $validated['locale'] ?? null,
                theme: $validated['theme'] ?? null,
                primaryColor: $validated['primary_color'] ?? null,
                neutralColor: $validated['neutral_color'] ?? null,
            )
        );

        return response()->json($result);
    }
}
