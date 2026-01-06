<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Src\IdentityAccess\Security\Reauth\UI\Middleware\ThrottleAuthEndpoints;
use App\Src\Shared\UI\Middleware\EnsureHorizonAccess;
use App\Src\Shared\UI\Middleware\SetLocaleFromRequest;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Keep the current session active but invalidate other browser sessions
        // when the user's password changes (used by Auth::logoutOtherDevices()).
        $middleware->web(append: [
            SetLocaleFromRequest::class,
            AuthenticateSession::class,
            ThrottleAuthEndpoints::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'horizon.access' => EnsureHorizonAccess::class,
            'locale' => SetLocaleFromRequest::class,
        ]);
    })
    ->withProviders([
        AppServiceProvider::class,
        HorizonServiceProvider::class,
        FortifyServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
