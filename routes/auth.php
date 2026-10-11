<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Fortify handles the POST /login, /register and /logout requests; these routes only render the pages.
Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return Inertia::render('auth/register');
    })->name('register');

    Route::get('/login', function () {
        return Inertia::render('auth/login', [
            'status' => session('status'),
        ]);
    })->name('login');
});
