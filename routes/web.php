<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceRequestController;
Route::redirect('/', '/settings/profile')->name('home');


Route::middleware('auth')->group(function () 
    {
        Route::get('/requests', [ServiceRequestController::class, 'index'])
        ->name('requests.index');
        Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->whereNumber('serviceRequest')
        ->name('requests.show');
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
