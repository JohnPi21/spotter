<?php

namespace App\Http\Controllers;

use App\Actions\DayExercise\CreateDayExercise;
use App\Actions\DayExercise\ReorderDayExercises;
use App\Http\Requests\StoreDayExerciseRequest;
use App\Http\Requests\SwapDayExerciseRequest;
use App\Http\Requests\UpdateDayExerciseOrderRequest;
use App\Models\DayExercise;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DayExerciseController extends Controller
{
    public function store(StoreDayExerciseRequest $request, MesoDay $day): RedirectResponse
    {
        app(CreateDayExercise::class)->execute($request->validated('exercise_id'), $day);

        return redirect()->route('days.show', [
            'mesocycle' => $day->mesocycle,
            'day'       => $day,
        ]);
    }

    public function updateOrder(UpdateDayExerciseOrderRequest $request, MesoDay $day): RedirectResponse
    {
        app(ReorderDayExercises::class)->execute($day, $request->validated('order'));

        return to_route('days.show', ['mesocycle' => $day->mesocycle, 'day' => $day]);
    }

    public function replaceExercise(SwapDayExerciseRequest $request, MesoDay $day): RedirectResponse
    {
        dd($day);

        return to_route('days.show', ['mesocycle' => $day->mesocycle, 'day' => $day]);
    }

    public function destroy(MesoDay $day, DayExercise $dayExercise): RedirectResponse
    {
        Gate::authorize('owns', $day->mesocycle);

        $dayExercise->load('day.mesocycle');

        $dayExercise->day->ensureIsEditable();

        $dayExercise->delete();

        return redirect()->route('days.show', ['mesocycle' => $dayExercise->day->mesocycle, 'day' => $dayExercise->day]);
    }
}
