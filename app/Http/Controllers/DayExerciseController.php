<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\MesoDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DayExerciseController extends Controller
{
    public function store(Request $request, MesoDay $day): RedirectResponse
    {
        if ((int) $day->status === 1) {
            throw ValidationException::withMessages([
                'day_status' => 'This day is already completed and cannot be modified.',
            ]);
        }

        $validated = $request->validate([
            'exercise_id' => [
                'required',
                'exists:exercises,id',
            ]
        ]);

        $daysIds = MesoDay::where('mesocycle_id', $day->mesocycle_id)
            ->where('day_order', $day->day_order)
            ->pluck('id');

        // Fetch last positions in one query
        $lastPositions = DayExercise::whereIn('meso_day_id', $daysIds)
            ->select('meso_day_id', DB::raw('MAX(position) as max_position'))
            ->groupBy('meso_day_id')
            ->pluck('max_position', 'meso_day_id');

        $exercises = [];

        foreach ($daysIds as $dayID) {
            $position = $lastPositions[$dayID] ?? -1;

            $exercises[] = [
                'meso_day_id' => $dayID,
                'exercise_id' => $validated['exercise_id'],
                'position'    => $position + 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        DayExercise::insert($exercises);

        return redirect()->back();
    }
}
