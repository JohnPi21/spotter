<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesocycleController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\MesoDayController;
use App\Models\MesoDay;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('hello');
});

Route::controller(MesocycleController::class)->group(function () {

    Route::get('/mesocycles', 'index')->name('mesocycles');

    Route::get('/mesocycles/create', 'create')->name('mesocycles.create');

    Route::post('/mesocycles', 'store');

    Route::get('/mesocycles/{mesocycle}', 'show');

    Route::get('/mesocycles/{id}/edit', 'edit')->name('mesocycles.create');

    Route::put('/mesocycles/{id}', 'update');

    Route::patch('/mesocycles/{mesocycle}', 'activate');

    Route::delete('/mesocycles/{mesocycle}', 'destroy');
});

Route::controller(MesoDayController::class)->group(function () {
    Route::get('/mesocycles/{mesocycle}/day/{day}', 'show');
});

Route::patch('/sets/{set}', [ExerciseSetController::class, 'update']);


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
