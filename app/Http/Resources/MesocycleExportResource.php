<?php

namespace App\Http\Resources;

use App\Models\Mesocycle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesocycleExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return list<array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        /** @var Mesocycle $mesocycle */
        $mesocycle = $this->resource;

        return $mesocycle->days->map(fn ($day) => [
            'label' => $day->label,
            'week' => $day->week,
            'order' => $day->day_order,
            'exercises' => $day->dayExercises->map(fn ($dayExercise) => [
                'exercise' => $dayExercise->exercise->name,
                'muscle_group' => $dayExercise->exercise->muscleGroup->name,
                'type' => $dayExercise->exercise->exercise_type,
                'sets' => $dayExercise->sets->map(fn ($set) => [
                    'weight' => $set->weight,
                    'reps' => $set->reps,
                    'target_weight' => $set->target_weight,
                    'target_reps' => $set->target_reps,
                ])->all(),
            ])->all(),
        ])->all();
    }
}
