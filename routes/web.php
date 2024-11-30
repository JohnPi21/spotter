<?php

use App\Http\Controllers\MesocycleController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('hello');
});


Route::controller(MesocycleController::class)->group(function () {

    Route::get('/mesocycles', 'index');

    Route::get('/mesocycles/create', 'create');

    Route::post('/mesocycles', 'store');

    Route::get('/mesocycles/{id}', 'show');

    Route::get('mesocycles/{id}/edit', 'edit');

    Route::put('mesocycles/{id}', 'update');

    Route::delete('mesocycles/{id}', 'destroy');

});
