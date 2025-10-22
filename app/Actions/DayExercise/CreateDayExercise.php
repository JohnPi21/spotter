<?php

namespace App\Actions\DayExercise;

use App\Models\MesoDay;
use App\Models\ExerciseSet;
use Illuminate\Validation\ValidationException;
use Log;

class CreateDayExercise
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute(int $exerciseId, MesoDay $day)
    {
        $day->ensureIsEditable();

        if ($day->dayExercises->contains('exercise_id', $exerciseId)) {
            throw ValidationException::withMessages([
                'exercise_id' => "Exercise already exists on this day."
            ]);
        }

        $lastPosition = $day->dayExercises->max('position') ?? 0;

        $dayExercise = $day->dayExercises()->create([
            'exercise_id' => $exerciseId,
            'position'    => $lastPosition + 1,
        ]);

        ExerciseSet::create([
            'day_exercise_id' => $dayExercise->id,
        ]);
    }
}
