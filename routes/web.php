<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\RequestCommentController;
Route::redirect('/', '/requests')->name('home');


Route::middleware('auth')->group(function () 
    {
        Route::get('/requests', [ServiceRequestController::class, 'index'])
        ->name('requests.index');
        Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->whereNumber('serviceRequest')
        ->name('requests.show');
        Route::get('/requests/create', [ServiceRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [ServiceRequestController::class, 'store'])->name('requests.store');
        Route::post('/requests/{serviceRequest}/comments', [RequestCommentController::class, 'store'])
        ->whereNumber('serviceRequest')
        ->name('requests.comments.store');
        
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
