<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Exercise extends Model
{
    use HasFactory;

    public function muscleGroup(): BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class);
    }

    public function sets(): HasManyThrough
    {
        return $this->hasManyThrough(
            ExerciseSet::class,       // Final model
            DayExercise::class,       // Intermediate model
            'exercise_id',            // Foreign key on day_exercises table (links to this model)
            'day_exercise_id',        // Foreign key on exercise_sets table (links to exercises)
            'id',                     // Local key on this model (meso_days)
            'id'                      // Local key on day_exercises (links to exercises)
        );
    }
}
