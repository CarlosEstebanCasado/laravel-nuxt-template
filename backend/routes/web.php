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

if (app()->environment('local')) {
    Route::view('/docs/openapi', 'openapi.index')->name('openapi.docs');
    Route::get('/docs/openapi.yaml', function () {
        $paths = [
            base_path('../docs/openapi.yaml'),
            base_path('docs/openapi.yaml'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return response()->file($path, [
                    'Content-Type' => 'application/yaml',
                ]);
            }
        }

        abort(404);
    })->name('openapi.spec');
}
