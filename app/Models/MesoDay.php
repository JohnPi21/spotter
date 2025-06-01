<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\ValidationException;

class MesoDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function mesocycle(): BelongsTo
    {
        return $this->belongsTo(Mesocycle::class);
    }

    public function dayExercises(): HasMany
    {
        return $this->hasMany(DayExercise::class, 'meso_day_id', 'id');
    }

    // public function exercises(): HasManyThrough
    // {
    //     return $this->hasManyThrough(Exercise::class, DayExercise::class, 'meso_day_id', 'id', 'id', 'exercise_id');
    // }

    public function ensureIsEditable(): void
    {
        if ((int) $this->status === 1) {
            throw ValidationException::withMessages([
                'day_status' => 'This day is already completed and cannot be modified.',
            ]);
        }
    }
}
