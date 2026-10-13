<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceRequestController;
// The frontend treats /requests as home. Until the request routes exist, send users to the settings page.
Route::redirect('/', '/settings/profile')->name('home');


Route::middleware('auth')->group(function () 
    {
        Route::get('/requests', [ServiceRequestController::class, 'index'])->name('requests.index');
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
