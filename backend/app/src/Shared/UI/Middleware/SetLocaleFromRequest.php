<?php

declare(strict_types=1);

namespace App\Src\Shared\UI\Middleware;

use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\Shared\Domain\Service\ConfigProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocaleFromRequest
{
    public function __construct(
        private readonly UserPreferencesRepository $userPreferencesRepository,
        private readonly ConfigProvider $configProvider
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('Content-Language', App::getLocale());

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $supportedLocales = array_map(
            static fn ($locale) => (string) $locale,
            array_keys((array) $this->configProvider->get('app.supported_locales', []))
        );

        $headerLocale = $request->header('X-Locale');
        if ($headerLocale && in_array($headerLocale, $supportedLocales, true)) {
            return $headerLocale;
        }

        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = $request->user();
        if ($user !== null) {
            $authIdentifier = $user->getAuthIdentifier();
            $userId = is_numeric($authIdentifier) ? (int) $authIdentifier : null;

            if ($userId !== null) {
                $preferences = $this->userPreferencesRepository->find(new UserId($userId));

                if ($preferences && in_array($preferences->locale()->toString(), $supportedLocales, true)) {
                    return $preferences->locale()->toString();
                }
            }
        }

        $preferred = $request->getPreferredLanguage($supportedLocales);
        if (is_string($preferred) && in_array($preferred, $supportedLocales, true)) {
            return $preferred;
        }

        return $this->defaultLocale();
    }

    private function defaultLocale(): string
    {
        $value = $this->configProvider->get('app.locale');

        return is_string($value) && $value !== '' ? $value : 'es';
    }
}
