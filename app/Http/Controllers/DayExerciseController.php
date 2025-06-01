<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DayExerciseController extends Controller
{
    public function store(Request $request, MesoDay $day): RedirectResponse
    {
        $validated = $request->validate([
            'exercise_id' => [
                'required',
                'exists:exercises,id',
                Rule::unique('day_exercises')->where('meso_day_id', $day->id),
            ]
        ]);

        if ((int) $day->status === 1) {
            throw ValidationException::withMessages([
                'day_status' => 'This day is already completed and cannot be modified.',
            ]);
        }

        $lastPosition = DayExercise::where('meso_day_id', $day->id)->orderBy('position', 'DESC')->value('position') ?? -1;

        // Maybe send the object back?
        DayExercise::create([
            'meso_day_id' => $day->id,
            'exercise_id' => $validated['exercise_id'],
            'position'    => $lastPosition + 1,
        ]);

        return redirect()->back();
    }
}
