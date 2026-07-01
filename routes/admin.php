<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::middleware(EnsureAdmin::class)->prefix('panel')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index']);

        Route::get('/users', [UserController::class, 'index'])->name('admin.users.view');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    });
});
