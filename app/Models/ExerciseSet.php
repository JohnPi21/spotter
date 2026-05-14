<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ExerciseSet extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'weight' => 'decimal:2',
        'target_reps' => 'int',
        'reps' => 'int',
    ];

    /**
     * @return BelongsTo<DayExercise, $this>
     */
    public function dayExercise(): BelongsTo
    {
        return $this->belongsTo(DayExercise::class);
    }

    /**
     * @return HasOneThrough<MesoDay, DayExercise, $this>
     */
    public function day(): HasOneThrough
    {
        return $this->hasOneThrough(
            MesoDay::class,
            DayExercise::class,
            'meso_day_id', // FK on day_exercises table to meso_days
            'day_exercise_id' // FK on exercise_sets table to day_exercises
        );
    }

    #[Scope]
    protected function ownedBy(Builder $query, int $userId)
    {
        return $query->whereHas('dayExercise.day.mesocycle', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
