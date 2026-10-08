<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/requests')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{serviceRequest}', [RequestController::class, 'show'])->name('requests.show');

    Route::post('/requests/{serviceRequest}/status', [RequestController::class, 'updateStatus'])->name('requests.status');
    Route::post('/requests/{serviceRequest}/take', [RequestController::class, 'take'])->middleware('role:staff')->name('requests.take');
    Route::post('/requests/{serviceRequest}/offer', [RequestController::class, 'offer'])->middleware('role:staff,management')->name('requests.offer');
    Route::post('/requests/{serviceRequest}/comments', [RequestController::class, 'comment'])->name('requests.comment');
    Route::post('/assignments/{assignment}/respond', [RequestController::class, 'respond'])->middleware('role:staff')->name('assignments.respond');

    // FR-007: management only
    Route::get('/dashboard', DashboardController::class)->middleware('role:management')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
