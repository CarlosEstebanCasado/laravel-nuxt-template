<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers;

use App\Src\IdentityAccess\Auth\User\Application\Request\OAuthCallbackUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\OAuthCallbackUseCase;
use App\Src\Shared\UI\Controllers\Controller;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends Controller
{
    /**
     * Providers allowed for OAuth authentication.
     *
     * @var array<int, string>
     */
    private array $providers = ['google', 'github'];

    public function __construct(
        private readonly OAuthCallbackUseCase $callbackUseCase
    ) {
    }

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $exception) {
            return $this->redirectToFrontend($provider, 'error', $exception->getMessage());
        }

        if (empty($socialUser->getEmail())) {
            return $this->redirectToFrontend($provider, 'error', 'email_missing');
        }

        $userId = $this->callbackUseCase->execute(new OAuthCallbackUseCaseRequest(
            provider: $provider,
            email: (string) $socialUser->getEmail(),
            name: $socialUser->getName(),
            nickname: $socialUser->getNickname(),
        ));

        $user = User::query()->findOrFail($userId->toInt());

        Auth::login($user, true);

        return $this->redirectToFrontend($provider, 'success');
    }

    private function ensureProviderIsSupported(string $provider): void
    {
        abort_unless(in_array($provider, $this->providers, true), Response::HTTP_NOT_FOUND);
    }

    private function redirectToFrontend(string $provider, string $status, ?string $error = null): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.frontend_url', config('app.url')), '/');
        $query = array_filter([
            'provider' => $provider,
            'status' => $status,
            'error' => $error,
        ]);

        $url = $frontendUrl.'/auth/callback';

        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        return redirect()->away($url);
    }
}
