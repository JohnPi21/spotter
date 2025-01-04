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
        return $this->hasManyThrough(ExerciseSet::class, DayExercise::class, 'exercise_id', 'id', 'id', 'exercise_set_id');
    }
}
