<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers;

use App\Src\Shared\UI\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class OAuthRedirectController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $providers = ['google', 'github'];

    public function __invoke(string $provider): RedirectResponse
    {
        $this->ensureProviderIsSupported($provider);

        return Socialite::driver($provider)->redirect();
    }

    private function ensureProviderIsSupported(string $provider): void
    {
        abort_unless(in_array($provider, $this->providers, true), Response::HTTP_NOT_FOUND);
    }
}
