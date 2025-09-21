<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\ExerciseSet;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;


class DayExerciseController extends Controller
{
    public function store(Request $request, MesoDay $day): RedirectResponse
    {
        // Authorize
        Gate::authorize('owns', $day->mesocycle);

        $day->ensureIsEditable();

        $validated = $request->validate([
            'exercise_id' => [
                'required',
                'exists:exercises,id',
                Rule::unique('day_exercises')->where('meso_day_id', $day->id),
            ]
        ]);

        $lastPosition = DayExercise::where('meso_day_id', $day->id)->orderBy('position', 'DESC')->value('position') ?? 0;

        // Maybe send the object back?
        $dayExercise = DayExercise::create([
            'meso_day_id' => $day->id,
            'exercise_id' => $validated['exercise_id'],
            'position'    => $lastPosition + 1,
        ]);

        ExerciseSet::create([
            'day_exercise_id' => $dayExercise->id,
        ]);

        return redirect()->route('days.show', [
            'mesocycle' => $day->mesocycle,
            'day'       => $day,
        ]);
    }

    public function updateOrder(Request $request, MesoDay $day): RedirectResponse
    {
        $day->load('mesocycle');

        Gate::authorize('owns', $day->mesocycle);

        $order = $request->validate([
            'order' => ['required', 'array', 'distinct', 'list'],
            'order.*' => ['required', 'integer'],
        ])['order'];

        foreach ($order as $position => $id) {
            DayExercise::where('id', $id)->update(['position' => $position + 1]);
        }

        return to_route('days.show', [
            'mesocycle' => $day->mesocycle,
            'day'       => $day,
        ]);
    }

    public function destroy(MesoDay $day, DayExercise $exercise): RedirectResponse
    {
        Gate::authorize('owns', $day->mesocycle);

        $exercise->load('day.mesocycle');

        $exercise->day->ensureIsEditable();

        $exercise->delete();

        return redirect()->route('days.show', [
            'mesocycle' => $exercise->day->mesocycle,
            'day'       => $exercise->day,
        ]);
    }
}
