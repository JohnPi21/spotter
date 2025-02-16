<?php

use App\Http\Controllers\MesocycleController;
use App\Http\Controllers\ExerciseSetController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('hello');
});


Route::controller(MesocycleController::class)->group(function () {

    Route::get('/mesocycles', 'index')->name('mesocycles');

    Route::get('/mesocycles/create', 'create')->name('mesocycles.create');

    Route::post('/mesocycles', 'store');

    Route::get('/mesocycles/{mesocycle}', 'show');

    Route::get('/mesocycles/{mesocycle}/day/{day}', 'getDay');

    Route::get('/mesocycles/{id}/edit', 'edit')->name('mesocycles.create');

    Route::put('/mesocycles/{id}', 'update');

    Route::delete('/mesocycles/{id}', 'destroy');
});

Route::patch('/sets/{set}', [ExerciseSetController::class, 'update']);
