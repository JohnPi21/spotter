<?php

namespace App\Http\Controllers;

use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        dd($validated);


        return redirect()->back();
    }
}
