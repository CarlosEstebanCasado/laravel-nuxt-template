<?php

use App\Src\IdentityAccess\Auth\User\UI\Controllers\OAuthCallbackController;
use App\Src\IdentityAccess\Auth\User\UI\Controllers\OAuthRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    abort(404);
});

// Ruta de login para Horizon (redirige al frontend)
Route::get('/login', function () {
    return redirect(config('app.frontend_url').'/auth/login');
})->name('login');

Route::prefix('auth/oauth')->group(function (): void {
    Route::get('{provider}', OAuthRedirectController::class)
        ->name('oauth.redirect');

    Route::get('{provider}/callback', OAuthCallbackController::class)
        ->name('oauth.callback');
});
