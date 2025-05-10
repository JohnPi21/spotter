<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesocycleController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\MesoDayController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('hello');
});

Route::controller(MesocycleController::class)->group(function () {

    Route::get('/mesocycles', 'index')->name('mesocycles');

    Route::get('/mesocycles/create', 'create')->name('mesocycles.create');

    Route::get('/mesocycles/current-day', [MesocycleController::class, 'currentActiveDay'])->name('mesocycles.current');

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

Route::patch('/sets/{set}', [ExerciseSetController::class, 'update']);

Route::post('/sets', [ExerciseSetController::class, 'store']);

Route::delete('/sets/{set}', [ExerciseSetController::class, 'destroy']);


// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });


// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });


// require __DIR__ . '/auth.php';
