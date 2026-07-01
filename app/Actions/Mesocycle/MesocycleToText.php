<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;

class MesocycleToText
{
    public function execute(Mesocycle $mesocycle): string
    {
        $text = '';
        $currentWeek = null;

        foreach ($mesocycle->days as $day) {
            if ($currentWeek !== $day->week) {
                $currentWeek = $day->week;
                $text .= "Week {$day->week}\n\n";
            }

            $text .= "{$day->label}\n";

            foreach ($day->dayExercises as $dayExercise) {
                $text .= "\n{$dayExercise->exercise->muscleGroup->name} \n";
                $text .= "{$dayExercise->exercise->name} - {$dayExercise->exercise->exercise_type} \n";

                foreach ($dayExercise->sets as $index => $set) {
                    $setNumber = $index + 1;

                    if (! $set->weight || ! $set->reps) {
                        $text .= "- Set {$setNumber}: Not logged";
                    } else {
                        $text .= "- Set {$setNumber}: {$set->weight} {$mesocycle->unit} x {$set->reps} reps";
                    }

                    if ($set->min_reps || $set->max_reps) {
                        $text .= " | Target Reps: {$set->min_reps} - {$set->max_reps} \n";
                    } else {
                        $text .= "\n";
                    }
                }
            }

            $text .= "\n";
        }

        return $text;
    }
}
