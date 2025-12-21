<?php

use App\Http\Controllers\Api\Auth\CurrentUserController;
use App\Http\Controllers\Api\Auth\DeleteSessionController;
use App\Http\Controllers\Api\Auth\DeleteAccountController;
use App\Http\Controllers\Api\Auth\RevokeOtherSessionsController;
use App\Http\Controllers\Api\Auth\SessionsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
        ]);
    });

    // Session/user bootstrap endpoint.
    // We intentionally keep this available for authenticated but unverified users,
    // so the SPA can detect the session and redirect users to verify their email.
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', CurrentUserController::class);
    });

    // Protected API endpoints (verified email required).
    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::delete('/account', DeleteAccountController::class);
        Route::get('/sessions', SessionsController::class);
        Route::post('/sessions/revoke-others', RevokeOtherSessionsController::class);
        Route::delete('/sessions/{id}', DeleteSessionController::class);
    });
});
