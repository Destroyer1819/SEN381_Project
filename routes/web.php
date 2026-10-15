<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\RequestCommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestAssignmentController;
use App\Http\Controllers\RequestStatusController;

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
        
        Route::post('/requests/{serviceRequest}/take', [RequestAssignmentController::class, 'take'])
        ->whereNumber('serviceRequest')
        ->name('requests.take');

        Route::post('/requests/{serviceRequest}/status', [RequestStatusController::class, 'store'])
        ->whereNumber('serviceRequest')
        ->name('requests.status');
        
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
