<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExerciseSetController extends Controller
{
    public function store(Request $request)
    {
        $dayExerciseID = $request->validate([
            'day_exercise_id' => ['required', 'integer']
        ])['day_exercise_id'];

        $dayExercise = DayExercise::with('day.mesocycle:id,user_id,id')->findOrFail($dayExerciseID);

        Gate::authorize('owns', $dayExercise->day->mesocycle);

        ExerciseSet::create([
            'day_exercise_id' => $dayExercise->id,
            'status'          => 0,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, ExerciseSet $set): RedirectResponse
    {
        $set->load('dayExercise.day.mesocycle:id,user_id,id');

        Gate::authorize('update', $set->dayExercise->day->mesocycle);

        $validated = $request->validate([
            'reps'      => ['required', 'integer'],
            'weight'    => ['required', 'integer'],
            'status'    => ['nullable', 'integer'],
        ]);

        $set->update($validated);

        return to_route('day.show', [$set->dayExercise->day, $set->dayExercise->day->mesocycle]);
        // return response()->json([
        //     'set' => $set
        // ]);

        // @TODO: check out how to pass back data for the updated $set (serach for partial reload in inertia )
        // return redirect()->back()->with('success', 'Exercise set updated successfully!')->with('set', $set);
    }

    public function destroy(ExerciseSet $set)
    {
        Gate::authorize('update', $set->dayExercise->day->mesocycle);

        $set->delete();

        return redirect()->back();
    }
}
