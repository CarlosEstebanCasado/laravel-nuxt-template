<?php

namespace App\Providers;

use App\Src\Auth\User\Domain\Repository\UserRepository;
use App\Src\Auth\User\Infrastructure\EloquentUserRepository;
use App\Src\Audit\Audit\Domain\Repository\AuditRepository;
use App\Src\Audit\Audit\Infrastructure\OwenItAuditRepository;
use App\Src\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\Security\Reauth\Infrastructure\EloquentAccountRepository;
use App\Src\Session\Session\Domain\Repository\SessionRepository;
use App\Src\Session\Session\Infrastructure\DatabaseSessionRepository;
use App\Src\Shared\Shared\Domain\Service\AuditEventRecorder;
use App\Src\Shared\Shared\Domain\Service\PasswordHasher;
use App\Src\Shared\Shared\Domain\Service\RandomStringGenerator;
use App\Src\Shared\Shared\Infrastructure\OwenItAuditEventRecorder;
use App\Src\Shared\Shared\Infrastructure\LaravelPasswordHasher;
use App\Src\Shared\Shared\Infrastructure\LaravelRandomStringGenerator;
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
        $this->app->bind(RandomStringGenerator::class, LaravelRandomStringGenerator::class);
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
