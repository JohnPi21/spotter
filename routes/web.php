<?php

use App\Http\Controllers\DayExerciseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesocycleController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\MesoDayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExerciseController;


Route::middleware('auth')->group(function () {

    Route::controller(MesocycleController::class)->group(function () {
        Route::get('/', 'index')->name('mesocycles');
        Route::get('/mesocycles/create', 'create')->name('mesocycles.create');
        Route::get('/mesocycles/current-day', 'currentActiveDay')->name('mesocycles.current');
        Route::post('/mesocycles', 'store');
        Route::get('/mesocycles/{mesocycle}', 'show');
        Route::get('/mesocycles/{id}/edit', 'edit')->name('mesocycles.create');
        Route::put('/mesocycles/{id}', 'update');
        Route::patch('/mesocycles/{mesocycle}', 'activate');
        Route::delete('/mesocycles/{mesocycle}', 'destroy');
    });

    Route::controller(MesoDayController::class)->group(function () {
        Route::get('/mesocycles/{mesocycle}/day/{day}', 'show')->name('day.show');
        Route::patch('/day/{day}', 'toggleDay');
    });

    Route::controller(ExerciseSetController::class)->group(function () {
        Route::post('/sets', 'store');
        Route::patch('/sets/{set}', 'update');
        Route::delete('/sets/{set}', 'destroy');
    });

    Route::controller(DayExerciseController::class)->group(function () {
        Route::post('/day/{day}/exercises/', 'store');
        Route::delete('day/{day}/exercises/{exercise}', 'destroy');
    });

    Route::controller(ExerciseController::class)->group(function () {
        Route::get('/exercises', 'index');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});


require __DIR__ . '/auth.php';
