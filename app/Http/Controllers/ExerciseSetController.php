<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExerciseSetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'        => ['required', 'integer'],
            'reps'      => ['required', 'integer'],
            'weight'    => ['required', 'integer'],
            'status'    => ['nullable', 'integer'],
        ]);

        $set = ExerciseSet::find($validated['id']);

        $set->reps   = $validated['reps'];
        $set->weight = $validated['weight'];
        $set->status = $validated['status'];

        // Check how to handle inertia response back without refreshing page
        // return redirect()->back()->with('message', 'Exercise set updated successfully!');
        return response()->json([
            'message' => 'Item created successfully!',
            'data' => $set,
        ]);
    }
}
