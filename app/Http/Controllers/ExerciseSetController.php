<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExerciseSetController extends Controller
{
    public function store(Request $request): void
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

        // check how to handle inertia response back without refreshing page
        // Inertia::share([
        //     'flash' => function () {
        //         return [
        //             'success' => session('success'),
        //             'error' => session('error'),
        //         ];
        //     },
        // ]);
    }
}
