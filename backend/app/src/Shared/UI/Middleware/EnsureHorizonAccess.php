<?php

declare(strict_types=1);

namespace App\Src\Shared\UI\Middleware;

use App\Src\Shared\Domain\Service\ConfigProvider;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHorizonAccess
{
    public function __construct(
        private readonly AuthFactory $authFactory,
        private readonly ConfigProvider $configProvider
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var StatefulGuard $guard */
        $guard = $this->authFactory->guard();

        // Only allow access to authenticated users.
        if (! $guard->check()) {
            return redirect()->route('login');
        }

        /** @var \App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User|null $user */
        $user = $guard->user();
        if ($user === null) {
            return redirect()->route('login');
        }

        // In local/dev environments, it's usually enough to require authentication.
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        // In non-local environments, require explicit allow-listing.
        $allowedEmails = (array) $this->configProvider->get('horizon.allowed_emails', []);

        if (empty($allowedEmails) || ! in_array($user->email, $allowedEmails, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
