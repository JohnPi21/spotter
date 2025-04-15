<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExerciseSetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dayExerciseID' => 'required|int|exists:day_exercises,id'
        ]);

        $dayExerciseID = $validated['dayExerciseID'];

        $set = new ExerciseSet();

        $set->day_exercise_id = $dayExerciseID;
        $set->status          = 0;

        $set->save();

        return redirect()->back();
    }

    public function update(Request $request, ExerciseSet $set)
    {
        $validated = $request->validate([
            'reps'      => ['required', 'integer'],
            'weight'    => ['required', 'integer'],
            'status'    => ['nullable', 'integer'],
        ]);

        $set->reps   = $validated['reps'];
        $set->weight = $validated['weight'];
        $set->status = $validated['status'];

        $set->save();

        return response()->json([
            'set' => $set
        ]);
        // @TODO: check out how to pass back data for the updated $set (serach for partial reload in inertia )
        // return redirect()->back()->with('success', 'Exercise set updated successfully!')->with('set', $set);
    }
}
