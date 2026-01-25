<?php

use App\Http\Controllers\DayExerciseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesocycleController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\MesoDayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\DashboardController as Dashboard;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');
    Route::controller(MesocycleController::class)->group(function () {
        Route::get('/mesocycles', 'index')->name('mesocycles');
        Route::get('/mesocycles/create', 'create')->name('mesocycles.create');
        Route::get('/mesocycles/current-day', 'currentActiveDay')->name('mesocycles.current');
        Route::post('/mesocycles', 'store')->name('mesocycles.store');
        Route::get('/mesocycles/{mesocycle}/edit', 'edit')->name('mesocycles.edit');
        Route::put('/mesocycles/{id}', 'update')->name('mesocycles.update');
        Route::patch('/mesocycles/{mesocycle}', 'activate')->name('mesocycles.activate');
        Route::delete('/mesocycles/{mesocycle}', 'destroy')->name('mesocycles.destroy');
    });

    Route::controller(MesoDayController::class)->group(function () {
        Route::get('/mesocycles/{mesocycle}/days/{day}', 'show')->name('days.show');
        Route::patch('/day/{day}', 'toggleDay')->name('days.toggle');
    });

    Route::controller(ExerciseSetController::class)->group(function () {
        Route::post('/day-exercises/{dayExercise}/sets', 'store')->name('sets.store');
        Route::patch('/day-exercises/{dayExercise}/sets/{set}', 'update')->name('sets.update');
        Route::delete('/day-exercises/{dayExercise}/sets/{set}', 'destroy')->name('sets.destroy');
    })->scopeBindings();

    Route::controller(DayExerciseController::class)->group(function () {
        Route::post('/day/{day}/exercises', 'store')->name('dayExercises.store');
        Route::patch('/day/{day}/reorder', 'updateOrder')->name('dayExercises.reorder');
        Route::patch('/day/{day}/replace', 'replace')->name('dayExercises.replace');
        Route::put('/day/{day}/save-note', 'saveNote')->name('dayExercises.saveNote');
        Route::delete('/day/{day}/note', 'deleteNote')->name('dayExercises.deleteNote');
        Route::delete('day/{day}/exercises/{dayExercise}', 'destroy')->name('dayExercise.destroy')->scopeBindings();
    });

    Route::controller(ExerciseController::class)->group(function () {
        Route::get('/exercises', 'index');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Get Globally available metadata once (init global data)
    Route::get("/meta", function () {
        return response()->json([
            // 'exercisesByMuscle' => MuscleGroup::with('exercises')->get(),
        ]);
    });

    Route::get('/boom', fn() => throw new \RuntimeException('Test Sentry'));
});


Route::get('/', fn() => Inertia::render('Landing'))->name('landing');

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'time' => now()], 200);
});


require __DIR__ . '/auth.php';

Route::fallback(function (Request $request) {
    return Inertia::render('ErrorPage', [
        'status' => 404,
        'message' => "Page not found."
    ])->toResponse($request)->setStatusCode(404);
});
