<?php

use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    abort(404);
});

// Ruta de login para Horizon (redirige al frontend)
Route::get('/login', function () {
    return redirect(config('app.frontend_url') . '/auth/login');
})->name('login');

Route::prefix('auth/oauth')->group(function (): void {
    Route::get('{provider}', [OAuthController::class, 'redirect'])
        ->name('oauth.redirect');

    Route::get('{provider}/callback', [OAuthController::class, 'callback'])
        ->name('oauth.callback');
});
