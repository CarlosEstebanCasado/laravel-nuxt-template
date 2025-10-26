<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta de login para Horizon (redirige al frontend)
Route::get('/login', function () {
    return redirect(config('app.frontend_url') . '/auth/login');
})->name('login');
