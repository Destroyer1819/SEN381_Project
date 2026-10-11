<?php

use Illuminate\Support\Facades\Route;

// The frontend treats /requests as home. Until the request routes exist, send users to the settings page.
Route::redirect('/', '/settings/profile')->name('home');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
