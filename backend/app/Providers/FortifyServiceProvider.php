<?php

namespace App\Providers;

use App\BoundedContext\Auth\User\UI\Fortify\CreateNewUserAction;
use App\BoundedContext\Auth\User\UI\Fortify\ResetUserPasswordAction;
use App\BoundedContext\Auth\User\UI\Fortify\UpdateUserPasswordAction;
use App\BoundedContext\Auth\User\UI\Fortify\UpdateUserProfileInformationAction;
use App\BoundedContext\Auth\User\UI\Responses\LoginResponse as ApiLoginResponse;
use App\BoundedContext\Auth\User\UI\Responses\LogoutResponse as ApiLogoutResponse;
use App\BoundedContext\Auth\User\UI\Responses\RegisterResponse as ApiRegisterResponse;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, ApiLoginResponse::class);
        $this->app->singleton(RegisterResponse::class, ApiRegisterResponse::class);
        $this->app->singleton(LogoutResponse::class, ApiLogoutResponse::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponse::class, \App\BoundedContext\Auth\User\UI\Responses\SuccessfulPasswordResetLinkRequestResponse::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, \App\BoundedContext\Auth\User\UI\Responses\FailedPasswordResetLinkRequestResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, \App\BoundedContext\Auth\User\UI\Responses\PasswordResetResponse::class);
        $this->app->singleton(FailedPasswordResetResponseContract::class, \App\BoundedContext\Auth\User\UI\Responses\FailedPasswordResetResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUserAction::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformationAction::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPasswordAction::class);
        Fortify::resetUserPasswordsUsing(ResetUserPasswordAction::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = rtrim(config('app.frontend_url'), '/');

            return $frontendUrl.'/reset-password/'.$token.'?email='.urlencode($notifiable->getEmailForPasswordReset());
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
