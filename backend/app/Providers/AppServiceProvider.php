<?php

namespace App\Providers;

use App\BoundedContext\Auth\User\Domain\Repository\UserRepository;
use App\BoundedContext\Auth\User\Infrastructure\EloquentUserRepository;
use App\BoundedContext\Audit\Audit\Domain\Repository\AuditRepository;
use App\BoundedContext\Audit\Audit\Infrastructure\OwenItAuditRepository;
use App\BoundedContext\Security\Reauth\Domain\Repository\AccountRepository;
use App\BoundedContext\Security\Reauth\Infrastructure\EloquentAccountRepository;
use App\BoundedContext\Session\Session\Domain\Repository\SessionRepository;
use App\BoundedContext\Session\Session\Infrastructure\DatabaseSessionRepository;
use App\BoundedContext\Shared\Shared\Domain\Service\AuditEventRecorder;
use App\BoundedContext\Shared\Shared\Domain\Service\PasswordHasher;
use App\BoundedContext\Shared\Shared\Infrastructure\OwenItAuditEventRecorder;
use App\BoundedContext\Shared\Shared\Infrastructure\LaravelPasswordHasher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(AuditEventRecorder::class, OwenItAuditEventRecorder::class);
        $this->app->bind(SessionRepository::class, DatabaseSessionRepository::class);
        $this->app->bind(AccountRepository::class, EloquentAccountRepository::class);
        $this->app->bind(AuditRepository::class, OwenItAuditRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
