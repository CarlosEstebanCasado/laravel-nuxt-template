<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends Controller
{
    /**
     * Providers allowed for OAuth authentication.
     *
     * @var array<int, string>
     */
    private array $providers = ['google'];

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

        $user = User::firstOrNew(['email' => $socialUser->getEmail()]);

        if (! $user->exists) {
            $user->name = $socialUser->getName() ?: $socialUser->getNickname() ?: $socialUser->getEmail();
            $user->password = Hash::make(Str::random(32));
        }

        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }

        $user->save();

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
