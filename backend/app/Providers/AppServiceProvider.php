<?php

namespace App\Providers;

use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorAuthenticationService;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorRecoveryCodesService;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Repository\EloquentUserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Repository\EloquentUserRepository;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify\FortifyTwoFactorAuthenticationService;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify\FortifyTwoFactorRecoveryCodesService;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use App\Src\IdentityAccess\Audit\Infrastructure\Eloquent\Repository\EloquentAuditRepository;
use App\Src\IdentityAccess\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\IdentityAccess\Security\Reauth\Infrastructure\Eloquent\Repository\EloquentAccountRepository;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\IdentityAccess\Session\Infrastructure\DatabaseSessionRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;
use App\Src\Shared\Domain\Service\ConfigProvider;
use App\Src\Shared\Domain\Service\RandomStringGenerator;
use App\Src\Shared\Infrastructure\LaravelConfigProvider;
use App\Src\Shared\Infrastructure\OwenItAuditEventRecorder;
use App\Src\Shared\Infrastructure\LaravelRandomStringGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(RandomStringGenerator::class, LaravelRandomStringGenerator::class);
        $this->app->bind(AuditEventRecorder::class, OwenItAuditEventRecorder::class);
        $this->app->bind(ConfigProvider::class, LaravelConfigProvider::class);
        $this->app->bind(SessionRepository::class, DatabaseSessionRepository::class);
        $this->app->bind(AccountRepository::class, EloquentAccountRepository::class);
        $this->app->bind(AuditRepository::class, EloquentAuditRepository::class);
        $this->app->bind(UserPreferencesRepository::class, EloquentUserPreferencesRepository::class);
        $this->app->bind(TwoFactorAuthenticationService::class, FortifyTwoFactorAuthenticationService::class);
        $this->app->bind(TwoFactorRecoveryCodesService::class, FortifyTwoFactorRecoveryCodesService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
