<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\RequestCommentController;
use App\Http\Controllers\DashboardController;

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

        Route::get('/dashboard', DashboardController::class)
        ->can('view-dashboard')
        ->name('dashboard');
        
        
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
