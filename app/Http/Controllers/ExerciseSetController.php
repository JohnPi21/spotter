<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Http\Request;

class ExerciseSetController extends Controller
{
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
